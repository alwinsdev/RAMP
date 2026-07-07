# SECURITY.md — RAMP security rules & checklist

> Build-time security guardrails for RAMP. Condensed from the enterprise ruleset in
> `docs/SECURITY_RULES.md` and **scoped to what this app actually is**: a read-only,
> mock-JSON Laravel 12 + Livewire 3 POC with role-based access. Read this before
> touching auth, routing, the data layer, headers, or anything that renders data.
> Full audit + rationale: [SECURITY_AUDIT.md](SECURITY_AUDIT.md).

## Golden rules for this codebase

1. **Authorization lives in the service layer, not the route.** Every asset/hierarchy
   read goes through `AssetService`/`Scope`. Never fetch and return an entity by a
   client-supplied id without `Scope::allowsAsset()` (or an equivalent scope check).
   Returning `null` → the component redirects. This is the app's one live risk class
   (IDOR) — keep it airtight.
2. **The UI never trusts client data for identity or scope.** Role/district/zone/
   panchayat come from the authenticated user, never from a request field.
3. **Escape everything rendered.** Blade `{{ }}` (auto-escaped) for data; `{!! !!}`
   only for hardcoded system SVG constants — **never** for model/request/API data.
   When you build HTML in JS (map popups), escape values + `encodeURIComponent` ids.
4. **No secrets in code, logs, or the bundle.** Env-only, git-ignored. Remove secrets
   the moment a feature stops using them (we removed the Maps key after the OSM move).
5. **Don't reorder the trust boundary:** validate → authenticate → authorize → act.
6. **Don't over-engineer to production ERP** (CLAUDE.md §8), but never make a choice
   that *blocks* the Phase-2+ hardening in the re-audit triggers below.

## Applies now — keep these true (✅ = currently enforced)

- ✅ **AuthZ / IDOR:** scope-checked reads; sub-views redirect on out-of-scope ids.
  Tests: `AuthTest` (`…_outside_their_scope`, `…_hierarchy_node…`, `…_sibling_panchayat`).
- ✅ **Login brute-force:** throttled 5/(email+IP), 60s lockout, generic failure
  message (no user enumeration). `app/Livewire/Auth/Login.php`.
- ✅ **Security headers:** `SecurityHeaders` middleware — X-Frame-Options,
  X-Content-Type-Options, Referrer-Policy, Permissions-Policy, X-XSS-Protection:0,
  CSP, and HSTS on secure production responses. Tests: `SecurityTest`.
- ✅ **CSP origins locked down:** `default-src 'self'`; scripts self only
  (`'unsafe-inline'/'unsafe-eval'` required by Livewire/Alpine); images self + OSM
  tiles; `object-src 'none'`; `base-uri`/`frame-ancestors 'self'`.
- ✅ **Route-param safety:** id params constrained by `[A-Za-z0-9\-]{1,40}` → 404 on
  malformed input. Keep this `where()` on every id route.
- ✅ **No open redirects:** all redirects target named routes.
- ✅ **Session:** httpOnly + SameSite=lax + auto-secure + `SESSION_ENCRYPT=true`.
- ✅ **Secrets:** `.env`/`.env.*`/`*.key`/`*.pem` git-ignored; `APP_KEY` set.
- ✅ **Map popups:** values HTML-escaped, ids `encodeURIComponent`, colour hex-validated.

## Not applicable while the POC stays read-only + mock

SQL injection · mass assignment · migrations/soft-delete · financial integrity &
idempotency · maker-checker · file uploads · exports · queue jobs · real API
throttling · audit trail. **Do not build these** (CLAUDE.md §2.2) — but see triggers.

## Deployment gate (must be verified before any hosted/prod deploy)

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] Served over **HTTPS** (so HSTS + secure cookies activate)
- [ ] `composer audit` and `npm audit` reviewed
- [ ] `.env` present only on the server, never committed
- [ ] File perms: dirs `775`, files `664`, `.env` `640`

See `docs/handover/05-deployment-checklist.md`.

## Re-audit triggers — flip N/A rules back on BEFORE you build the feature

| If you add… | Re-apply rules |
|---|---|
| Database / Eloquent | Mass assignment (5), SQLi (6), global tenant scope (14), DB integrity (19) |
| Write operations (create/edit/delete) | CSRF-on-writes (8), transactions/locks & idempotency (13), audit trail (15) |
| A real backend API | Rate limiting (12), API auth/scopes + no trace leakage (20) |
| File uploads / exports | Upload validation (9), export authz + CSV formula-injection (21) |
| Real authentication | 2FA, lockout policy, session lifetime, password hashing policy (3) |

> When any trigger fires: update [SECURITY_AUDIT.md](SECURITY_AUDIT.md), add tests, and
> re-run the relevant enterprise rules from `docs/SECURITY_RULES.md`.
