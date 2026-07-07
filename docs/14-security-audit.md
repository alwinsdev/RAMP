# Security Audit — RAMP POC (OWASP Top 10)

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-14 |
| Scope | Phase 1 POC — Laravel 12 · Livewire 3 · mock JSON · read-only · **mock auth + role-based scoping** |
| Method | OWASP Top 10 (2021) review + the enterprise ruleset (`docs/SECURITY_RULES.md`) + Laravel/Livewire checks + dependency audit |
| Result | No high/critical findings. Hardening applied; residual items are deployment-config. |
| Companion | Senior-engineer rule-by-rule audit + living checklist: [`.claude/SECURITY_AUDIT.md`](../.claude/SECURITY_AUDIT.md), [`.claude/SECURITY.md`](../.claude/SECURITY.md) |

> The POC is **read-only**, has **no database**, and accepts **no write input** — which removes whole classes of risk (SQLi, mass assignment, write-side IDOR, financial integrity). It **does** have **mock authentication with role-based row scoping** (`App\Support\Auth\Scope`), so read-side access control / IDOR **is** in scope and was audited in depth. Maps use **Leaflet + OpenStreetMap** (no API key). The review focused on: authorization/IDOR, auth brute-force, output handling, headers, input constraints, data-layer exposure, secrets, and dependencies.

---

## 1. OWASP Top 10 findings

| # | Category | Status | Notes |
|---|---|---|---|
| A01 | Broken Access Control | ✅ Pass (enforced + tested) | **Role-based scoping** in the service layer (`AssetService`/`Scope`): a district/panchayat officer cannot read another area's asset by guessing its id — `detail()` returns `null` and the sub-view redirects; hierarchy single-node lookups are scope-aware. Proven by `AuthTest` (`…_outside_their_scope`, `…_hierarchy_node…`, `…_sibling_panchayat`). **Fixed:** `removeFilter()` whitelisted to declared filters (was `property_exists()`); route-parameter regex constraints; defence-in-depth scope guard on `AssetService::photos()`. |
| A02 | Cryptographic Failures | ✅ Pass | `APP_KEY` set; Livewire snapshots signed with it. `SESSION_ENCRYPT=true`. No secrets in the dataset. Maps moved to keyless Leaflet/OSM, so the former `GOOGLE_MAPS_API_KEY` secret was **removed** from config and `.env` (dead-secret elimination). `.env`/`*.key`/`*.pem` git-ignored. |
| A03 | Injection | ✅ Pass | No SQL (no DB). JSON is read only by the data layer with **hardcoded collection names** — no user input reaches file paths (no path traversal). Blade auto-escapes all output; the only `{!! !!}` sinks render trusted hardcoded SVGs, never user data. Map popups build HTML in JS — **hardened** with `escapeHtml()` on all values + `encodeURIComponent()` on ids (defensive for the Phase-2 live API). Filter values reflected in chips use escaped `{{ }}`. |
| A04 | Insecure Design | ✅ Pass | Layered architecture; data seam; computed-not-stored status; authorization in the service layer (not just middleware). No risky flows. |
| A05 | Security Misconfiguration | ✅ Fixed | Baseline security headers (CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy) via `SecurityHeaders` middleware, **plus HSTS** on secure production responses. CSP locks origins to **self + OpenStreetMap tiles** (Google Maps fully removed). **Disabled** the unused `local` disk's public file-serving route. Mock data lives in `storage/` (never web-accessible). ⚠️ Ensure `APP_DEBUG=false` and `APP_ENV=production` on deploy. |
| A06 | Vulnerable & Outdated Components | ✅ Pass | `composer audit`: **0 advisories**. `npm audit`: low, dev-server-only advisory — not in production build artifacts; mitigated by binding the dev server to localhost. Maps dependency is now Leaflet/OSM (no third-party JS at runtime beyond bundled self). |
| A07 | Identification & Auth Failures | ✅ Pass (mock) | Mock session auth (`users.json`). **Brute-force throttling** added (5 attempts / email+IP, 60s lockout); **generic failure messages** (no user enumeration); `session()->regenerate()` on login. 2FA / email verification / password policy = Phase 5. |
| A08 | Software & Data Integrity Failures | ✅ Pass | No deserialization of untrusted input; JSON decoded from trusted local files only. Livewire request snapshots are integrity-checksummed (APP_KEY). |
| A09 | Logging & Monitoring Failures | ➖ Deferred | POC scope; production logging/monitoring is a deployment concern. |
| A10 | SSRF | ✅ Pass | No server-side fetching of user-controlled URLs. Images are local (`public/asset-images/`); map tiles load client-side from fixed OpenStreetMap hosts. |

---

## 2. Fixes applied (this audit)

1. **`SecurityHeaders` middleware** (`app/Http/Middleware/SecurityHeaders.php`), appended to the `web` group:
   - `Content-Security-Policy` — `default-src 'self'`; scripts allow `'unsafe-inline'/'unsafe-eval'` (a Livewire+Alpine requirement) but **lock origins**: self + bunny fonts + **OpenStreetMap tiles** (`*.tile.openstreetmap.org`); `object-src 'none'`, `base-uri 'self'`, `frame-ancestors 'self'`, `form-action 'self'`.
   - `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (geolocation/camera/mic/payment/usb denied), `X-XSS-Protection: 0`, and **`Strict-Transport-Security`** on secure production responses.
   - Toggle: `RAMP_SECURITY_HEADERS=false` (debug only).
2. **Login brute-force throttling** — `Login` throttles 5 attempts per (email + IP) with a 60s lockout and a generic, non-enumerating failure message.
3. **Map popup XSS hardening** — HTML built in JS escapes all marker values and `encodeURIComponent`s the id (defensive for the Phase-2 live API).
4. **Dead-secret removal** — the now-unused `GOOGLE_MAPS_API_KEY` was removed from `config/ramp.php`, `LocationView`, and `.env*` after the keyless Leaflet/OSM migration. `SESSION_ENCRYPT=true`.
5. **Defence-in-depth scope guard** — `AssetService::photos()` returns `[]` for an out-of-scope asset id.
6. **Filter whitelist** — `AssetList::removeFilter()`/`resetFilters()` operate on an allow-list constant, not `property_exists()`.
7. **Route parameter constraints** — every id param is constrained to `[A-Za-z0-9\-]{1,40}`; malformed paths 404 before hitting a component.
8. **Least privilege** — `local` filesystem disk `serve => false` (no public route over private storage).
9. **Dependency hygiene** — ran `composer audit` (clean) and `npm audit`.

Covered by `tests/Feature/SecurityTest.php` (headers present, CSP locks origins to self + OSM with no stale Google origins, malformed params rejected, removeFilter whitelist) and `tests/Feature/AuthTest.php` (RBAC/IDOR scoping, login lockout after 5 failures). Full suite: **110 passing**.

---

## 3. Residual / deployment checklist (not code)

- [ ] `APP_DEBUG=false`, `APP_ENV=production` in the production environment.
- [ ] Serve over **HTTPS** so the app's HSTS header and secure-cookie flags activate; set `TrustProxies` if behind a load balancer.
- [ ] Keep `.env` out of version control (already gitignored); rotate `APP_KEY`/`DB_PASSWORD` if ever shared in plaintext.
- [ ] Run the Vite dev server bound to `localhost` only (mitigates the low-severity esbuild advisory); it is absent from production builds.
- [ ] Re-run `composer audit` / `npm audit` in CI on every build.
- [ ] Phase 3+: when a database/write operations arrive, re-open the applicable enterprise rules (mass assignment, SQLi, transactions/idempotency, audit trail) — see the re-audit triggers in [`.claude/SECURITY_AUDIT.md`](../.claude/SECURITY_AUDIT.md).
- [ ] Phase 5: production AuthN/AuthZ + RBAC, 2FA, audit logging.

> **Note:** maps are keyless (Leaflet + OpenStreetMap) — there is no longer a Google Maps API key to restrict.

---

*End of Security Audit — RAMP-DOC-14*
