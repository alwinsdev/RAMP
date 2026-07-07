# RAMP — Security Audit

**Auditor role:** Senior Application Security Engineer
**Standard:** `docs/SECURITY_RULES.md` (Enterprise Laravel Security Rules v3.0, OWASP-aligned) + OWASP ASVS/Top-10
**Target:** RAMP POC — Laravel 12 · Livewire 3 · **mock JSON data, read-only**
**Date:** 2026-07-06
**Verdict:** ✅ **No exploitable vulnerabilities found.** All in-scope hardening items applied. Remaining checklist items are either **not applicable** to a read-only mock POC or are **deployment-time** controls (documented below).

---

## 1. Scope & threat model

RAMP is a **proof of concept**. This materially changes which rules apply:

| Property | Reality in RAMP | Consequence for the audit |
|---|---|---|
| Persistence | **None** — data is read from `storage/app/mock-data/*.json` | SQL injection, mass assignment, migrations, soft-delete rules = **N/A** |
| Writes | **None** — the app is read-and-display only | CSRF-on-writes, idempotency, maker-checker, financial integrity = **N/A** |
| Money | **None** — no settlements/ledgers/payments | All of Rule 13 = **N/A** |
| Auth | **Mock** session guard over `users.json` (3 fixed demo users) | Real 2FA/lockout policy = **partial** (throttling added; 2FA is Phase 5) |
| Multi-tenancy | Single dataset, **role-based row scoping** via `App\Support\Auth\Scope` | IDOR/authorization = **APPLICABLE and enforced** |
| File uploads | **None** | Rule 9 = **N/A** |
| Queues/jobs | **None** | Rule 16 = **N/A** |
| Third-party JS | Leaflet + OpenStreetMap tiles (no API key) | Reduced supply-chain/secret surface |

> The one class of access-control bug that **is** live here is **IDOR / broken object-level authorization** — a low-privilege officer trying to read another area's asset by guessing an ID. That was audited in depth and is correctly enforced (see §3).

---

## 2. Findings & remediation (this audit)

| # | Severity | Rule | Finding | Status |
|---|---|---|---|---|
| F-1 | **Medium** | 3 / 12 | Mock login had **no brute-force throttling** | ✅ Fixed — 5 attempts / (email+IP), 60s lockout, generic message |
| F-2 | Low | 17 | **HSTS** header absent | ✅ Fixed — `Strict-Transport-Security` emitted on secure production responses |
| F-3 | Low | 7 | Map popup built HTML by **string interpolation** of marker data (mock now, **live API in Phase 2**) | ✅ Fixed — `escapeHtml()` on all values, `encodeURIComponent()` on the id, hex-validated colour |
| F-4 | Low | 18 | `GOOGLE_MAPS_API_KEY` was a **live secret in `.env`**, now **unused** after the Leaflet/OSM migration | ✅ Fixed — removed from `config/ramp.php`, `LocationView`, `.env`, `.env.example` (dead secret eliminated) |
| F-5 | Low | 2 | Session payload not encrypted | ✅ Fixed — `SESSION_ENCRYPT=true` |
| F-6 | Info | 14 | `AssetService::photos()` had no scope check of its own (only ever reached via the already-scoped asset DTO) | ✅ Fixed — added a defence-in-depth scope guard (returns `[]` for out-of-scope ids) |

### Verified-correct (no change needed)

- **F-A · IDOR / object authorization (Rule 14):** `AssetService::detail()` returns `null` for any asset failing `Scope::allowsAsset()`, and every sub-view (Detail, Photos, Location, Lifecycle) redirects on `null` in `mount()`. Hierarchy single-node lookups (`districtById`/`zoneById`/`panchayatById`) are scope-aware. Proven by `AuthTest::test_officer_cannot_open_an_asset_outside_their_scope`, `…_hierarchy_node_outside_their_scope`, `…_sibling_panchayat`.
- **F-B · Enumeration (Rule 3.3):** login uses one generic message for unknown-user and wrong-password; forgot-password always acknowledges without revealing existence.
- **F-C · Open redirect (Rule 10):** every `redirect()`/`redirectIntended()` targets a named route — no user-controlled URLs.
- **F-D · XSS in Blade (Rule 7):** no `{!! !!}` renders user/model data. The three raw-output sites (`nav-icon`, `category-icon`, `kpi-card`) emit **hardcoded SVG path constants** selected from a whitelist map — not request/model input.
- **F-E · Route-param injection (Rule 6-adjacent):** all id route params are constrained by `[A-Za-z0-9\-]{1,40}`; malformed params 404 (`SecurityTest`).
- **F-F · Security headers (Rule 17):** `SecurityHeaders` middleware sets X-Frame-Options, X-Content-Type-Options, Referrer-Policy, Permissions-Policy, X-XSS-Protection:0, and a tight CSP on every web response.
- **F-G · Secrets in VCS (Rule 18):** `.env`, `.env.*`, `*.pem`, `*.key` are git-ignored; `APP_KEY` is set.

---

## 3. Rule-by-rule applicability matrix

| Rule | Topic | Applies? | Status |
|---|---|---|---|
| 1 | App basics (debug/key/deps) | Partial | `APP_KEY` set; `composer/npm audit` in checklist; `APP_DEBUG=false` is a **deploy gate** (dev stays `true`) |
| 2 | Cookies & session | Yes | ✅ Framework-secure defaults (httpOnly, SameSite=lax, auto-secure) + `SESSION_ENCRYPT=true` |
| 3 | Authentication | Partial | ✅ Throttle + generic errors added; 2FA/email-verify = Phase 5 (mock auth) |
| 4 | Authorization | Yes | ✅ Enforced in the **service layer** (`Scope`) — not just middleware; covered by tests |
| 5 | Mass assignment | **N/A** | No Eloquent/models/writes |
| 6 | SQL injection | **N/A** | No database/queries; route params regex-constrained |
| 7 | XSS | Yes | ✅ Blade auto-escaping; map popup hardened; CSP |
| 8 | CSRF | Partial | Livewire sends CSRF tokens on all component updates; no custom unsafe POST forms except `@csrf`-protected logout |
| 9 | File upload | **N/A** | No uploads |
| 10 | Path traversal / open redirect | Yes | ✅ No user-controlled file paths or redirects |
| 11 | Command/object injection | **N/A** | No `exec`/`eval`/`unserialize`/`extract` of input |
| 12 | Rate limiting | Partial | ✅ Login throttled; broad API throttling = Phase 2 (no API yet) |
| 13 | Financial integrity | **N/A** | No financial operations |
| 14 | IDOR / tenant isolation | Yes | ✅ Role scoping enforced + tested; photos guard added |
| 15 | Audit trail | **N/A (POC)** | No state changes to audit; real audit logging = Phase 3+ |
| 16 | Queue/job security | **N/A** | No jobs |
| 17 | Security headers | Yes | ✅ Full set + HSTS (prod) |
| 18 | Secret management | Yes | ✅ Env-only, git-ignored; dead maps key removed |
| 19 | DB integrity / soft delete | **N/A** | No database |
| 20 | API security | **N/A (POC)** | No API surface; `APP_DEBUG=false` deploy gate covers trace leakage |
| 21 | Export security | **N/A** | No exports (Reports module removed) |
| 22 | Cache security | Low | Cache holds only the login throttle counter (namespaced by email+IP) — no PII/authz decisions cached |
| 23 | Monitoring | **N/A (POC)** | SIEM/alerting = production concern |

---

## 4. Accepted / deferred (documented, not defects)

- **Local dev uses `APP_DEBUG=true`, `APP_ENV=local`.** Correct for development. Production deployment **must** set `APP_DEBUG=false`, `APP_ENV=production`, and serve over HTTPS — enforced by the [Deployment Checklist](../docs/handover/05-deployment-checklist.md) and re-stated in [SECURITY.md](SECURITY.md).
- **CSP allows `'unsafe-inline'`/`'unsafe-eval'` for scripts.** This is a hard requirement of Livewire 3 + Alpine (inline expression evaluation), not a choice. XSS is defended at the source by Blade auto-escaping; the CSP still restricts resource **origins** (self + OSM tiles only, `object-src 'none'`, `base-uri 'self'`, `frame-ancestors 'self'`).
- **Mock authentication** (no 2FA, no email verification, no password policy). By design for the POC; production auth/RBAC hardening is Phase 5.

---

## 5. Re-audit triggers (when this document must be revisited)

Revisit **before** shipping any of these, because they flip N/A rules to applicable:
- Introducing a **database/Eloquent** → Rules 5, 6, 14 (global scopes), 19.
- Introducing **write operations** → Rules 5, 8, 13, 15 (audit), idempotency.
- Introducing a **real API** → Rules 12 (throttling), 20.
- Introducing **file uploads / exports** → Rules 9, 21.
- Replacing mock auth → Rule 3 (2FA, lockout policy, session lifetime).

_See [SECURITY.md](SECURITY.md) for the living checklist._
