# Security Audit — RAMP POC (OWASP Top 10)

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-14 |
| Scope | Phase 1 POC — Laravel 12 · Livewire 3 · mock JSON · no database/auth/CRUD |
| Method | OWASP Top 10 (2021) review + Laravel/Livewire-specific checks + dependency audit |
| Result | No high/critical findings. Hardening applied; residual items are deployment-config. |

> The POC is **read-only**, has **no database**, **no authentication**, and accepts **no write input** — which removes whole classes of risk (SQLi, mass assignment, IDOR on writes, auth bypass). The review focused on what remains: output handling, headers, input constraints, data-layer exposure, and dependencies.

---

## 1. OWASP Top 10 findings

| # | Category | Status | Notes |
|---|---|---|---|
| A01 | Broken Access Control | ✅ Pass (by design) | No auth in Phase 1 — all data is public, read-only reference data. Route params validated by lookups → graceful redirect on unknown id (BR-NV-09). **Fixed:** `removeFilter()` previously used `property_exists()` (could clear any public/inherited property); now whitelisted to declared filters. Added route-parameter regex constraints. |
| A02 | Cryptographic Failures | ✅ Pass | `APP_KEY` set; Livewire snapshots signed with it. No secrets in the dataset. The Google Maps key is client-side by necessity (browser Maps JS) and lives in gitignored `.env` — restrict it by HTTP referrer + API in Google Cloud. |
| A03 | Injection | ✅ Pass | No SQL (no DB). JSON is read only by the data layer with **hardcoded collection names** — no user input reaches file paths (no path traversal). Blade auto-escapes all output; the only two `{!! !!}` sinks render trusted hardcoded SVGs, never user data. Filter values reflected in chips use escaped `{{ }}`. |
| A04 | Insecure Design | ✅ Pass | Layered architecture; data seam; computed-not-stored status. No risky flows. |
| A05 | Security Misconfiguration | ✅ Fixed | **Added** baseline security headers (CSP, X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy) via `SecurityHeaders` middleware. **Disabled** the unused `local` disk's public file-serving route (least privilege). Mock data lives in `storage/` (never web-accessible). ⚠️ Ensure `APP_DEBUG=false` and `APP_ENV=production` on deploy. |
| A06 | Vulnerable & Outdated Components | ✅ Pass | `composer audit`: **0 advisories**. `npm audit`: 1 **low**, dev-only (esbuild dev-server file-read on Windows) — not present in production build artifacts; mitigated by binding the dev server to localhost. No breaking upgrade forced. |
| A07 | Identification & Auth Failures | N/A (by design) | No authentication in Phase 1. Deferred to Phase 5 (AuthN/AuthZ + RBAC aligned to the hierarchy). |
| A08 | Software & Data Integrity Failures | ✅ Pass | No deserialization of untrusted input; JSON decoded from trusted local files only. Livewire request snapshots are integrity-checksummed (APP_KEY). |
| A09 | Logging & Monitoring Failures | ➖ Deferred | POC scope; production logging/monitoring is a deployment concern. |
| A10 | SSRF | ✅ Pass | No server-side fetching of user-controlled URLs. Images are local (`public/asset-images/`); the Maps script URL is fixed with the configured key. |

---

## 2. Fixes applied (this audit)

1. **`SecurityHeaders` middleware** (`app/Http/Middleware/SecurityHeaders.php`), appended to the `web` group:
   - `Content-Security-Policy` — `default-src 'self'`; scripts/styles allow `'unsafe-inline'/'unsafe-eval'` (a Livewire+Alpine requirement) but **lock origins**: only self + Google Maps + bunny fonts; `object-src 'none'`, `base-uri 'self'`, `frame-ancestors 'self'`, `form-action 'self'`.
   - `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: strict-origin-when-cross-origin`, `Permissions-Policy` (geolocation/camera/mic/payment/usb denied), `X-XSS-Protection: 0`.
   - Toggle: `RAMP_SECURITY_HEADERS=false` (debug only).
2. **Filter whitelist** — `AssetList::removeFilter()`/`resetFilters()` operate on an allow-list constant, not `property_exists()`.
3. **Route parameter constraints** — every id param is constrained to `[A-Za-z0-9\-]{1,40}`; malformed paths 404 before hitting a component.
4. **Least privilege** — `local` filesystem disk `serve => false` (no public route over private storage).
5. **Dependency hygiene** — ran `composer audit` (clean) and `npm audit fix`.

Covered by `tests/Feature/SecurityTest.php` (headers present, CSP locks origins, malformed params rejected, removeFilter whitelist). Full suite: **77 passing**.

---

## 3. Residual / deployment checklist (not code)

- [ ] `APP_DEBUG=false`, `APP_ENV=production` in the production environment.
- [ ] Serve over **HTTPS**; set `TrustProxies` if behind a load balancer; consider HSTS at the edge.
- [ ] **Restrict the Google Maps API key** by HTTP referrer and to the Maps JavaScript API in Google Cloud.
- [ ] Keep `.env` out of version control (already gitignored) and rotate any key shared in plaintext.
- [ ] Run the Vite dev server bound to `localhost` only (mitigates the low-severity esbuild advisory); it is absent from production builds.
- [ ] Re-run `composer audit` / `npm audit` in CI on every build.
- [ ] Phase 5: add AuthN/AuthZ + RBAC, rate limiting, audit logging, and re-run this checklist with write operations in scope.

---

*End of Security Audit — RAMP-DOC-14*
