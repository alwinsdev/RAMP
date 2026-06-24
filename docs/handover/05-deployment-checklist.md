# Deployment Checklist — RAMP POC

| Field | Value |
|---|---|
| Document | Deployment Checklist (POC / shared demo environment) |
| Audience | DevOps, Solution Architect |
| Scope | Hosting the **mock-data POC** for a shared/remote demo (no database) |
| Out of scope | Production deployment with DB/APIs (Phase 2/3 — see [Future Roadmap](07-future-roadmap.md)) |

> The POC has **no database, no queue worker, no cache server** dependencies. It is a stateless Laravel app reading mock JSON. Deployment is therefore lightweight, but the public-facing checks (HTTPS, debug off, key restriction) still matter.

---

## 1. Server prerequisites
| ID | Requirement | Notes | Done |
|---|---|---|---|
| DP-01 | PHP **8.2+** with `mbstring`, `openssl`, `gd` or `imagick` (favicons), `fileinfo` | `php -m` | |
| DP-02 | Composer 2.x | build-time only | |
| DP-03 | Node 18+ / npm | build-time only (assets compiled, not needed at runtime) | |
| DP-04 | Web server: Nginx or Apache (or `php artisan serve` behind a proxy for a quick demo) | document root = `public/` | |
| DP-05 | TLS certificate (HTTPS) | required for a public demo + Maps | |

## 2. Build & release
```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan key:generate           # once, persist APP_KEY
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
| ID | Step | Verify | Done |
|---|---|---|---|
| DP-10 | Production dependencies installed | no dev packages | |
| DP-11 | Assets built into `public/build` | manifest present | |
| DP-12 | Config/route/view caches warmed | faster boot | |

## 3. Environment (`.env`) — production-safe
| ID | Setting | Value | Done |
|---|---|---|---|
| DP-20 | `APP_ENV` | `production` | |
| DP-21 | `APP_DEBUG` | **`false`** (no stack traces) | |
| DP-22 | `APP_URL` | the HTTPS demo URL | |
| DP-23 | `APP_KEY` | set (kept secret) | |
| DP-24 | `SESSION_DRIVER` | `file` (or `cookie`) | |
| DP-25 | `RAMP_DATA_PROVIDER` | `mock` | |
| DP-26 | `RAMP_SECURITY_HEADERS` | `true` | |
| DP-27 | `GOOGLE_MAPS_API_KEY` | set **and referrer-restricted to the demo domain** | |

## 4. Filesystem & permissions
| ID | Step | Notes | Done |
|---|---|---|---|
| DP-30 | `storage/` and `bootstrap/cache/` writable by the web user | sessions, compiled views | |
| DP-31 | `storage/app/mock-data/*.json` present and readable | the data source | |
| DP-32 | `public/asset-images/` deployed | category photos | |
| DP-33 | `.env` **not** web-accessible / not in version control | secrets | |
| DP-34 | Document root points at `public/` (never the project root) | prevents source exposure | |

## 5. Web server hardening
| ID | Step | Notes | Done |
|---|---|---|---|
| DP-40 | Force HTTPS; enable HSTS at the edge | | |
| DP-41 | `TrustProxies` configured if behind a load balancer | correct scheme/IP | |
| DP-42 | Security headers active (app middleware sets CSP, X-Frame-Options, nosniff, Referrer-Policy, Permissions-Policy) | verify response headers | |
| DP-43 | Directory listing disabled | | |
| DP-44 | Block access to dotfiles (`.env`, `.git`) | | |

## 6. Security verification (from the security audit)
| ID | Step | Done |
|---|---|---|
| DP-50 | `composer audit` → no advisories | |
| DP-51 | `npm audit` reviewed (1 low, dev-server only — not in production build) | |
| DP-52 | Response includes `Content-Security-Policy`, `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff` | |
| DP-53 | Malformed asset URLs return 404 (route constraints) | |
| DP-54 | Google Maps key restricted by referrer + API | |
| DP-55 | Reference: [`docs/14-security-audit.md`](../14-security-audit.md) | |

## 7. Smoke test (post-deploy)
| ID | Step | Expected | Done |
|---|---|---|---|
| DP-60 | `GET /` (signed out) | redirects to `/login` over HTTPS | |
| DP-61 | Login with each demo account | correct scope per role | |
| DP-62 | Dashboard | 100 assets; reconciles | |
| DP-63 | Open an asset → Location | map renders (referrer ok) | |
| DP-64 | `php artisan test` (staging copy) | 94 passed | |

## 8. Rollback / operations
| ID | Step | Notes | Done |
|---|---|---|---|
| DP-70 | Tag the release / keep the previous build | quick rollback | |
| DP-71 | Clear caches on redeploy | `php artisan optimize:clear` then re-cache | |
| DP-72 | Basic uptime check on `/up` (Laravel health route) | monitoring | |

> **Note:** No migrations, seeders, queue workers, or scheduler are required for the POC. Those appear in Phase 3 when the database and write operations are introduced.

*End of Deployment Checklist.*
