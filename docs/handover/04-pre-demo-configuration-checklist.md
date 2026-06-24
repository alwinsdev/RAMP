# Pre-Demo Configuration Checklist — RAMP POC

| Field | Value |
|---|---|
| Document | Pre-Demo Configuration Checklist |
| Audience | Developer / Presenter setting up the demo |
| Goal | A clean, fast, error-free demo environment |
| Time | ~20–30 minutes the first time; ~5 minutes thereafter |

> Complete this **before** the [Stakeholder Demo Script](01-stakeholder-demo-script.md). The single highest-risk item is the **Google Maps API key referrer** (Step 5).

---

## 1. Toolchain
| ID | Step | Verify | Done |
|---|---|---|---|
| PD-01 | PHP 8.2+ installed | `php -v` | |
| PD-02 | Composer 2.x installed | `composer --version` | |
| PD-03 | Node 18+ / npm installed | `node -v` · `npm -v` | |

## 2. Install & build
```bash
composer install
npm install
cp .env.example .env          # if .env not already present
php artisan key:generate      # if APP_KEY empty
npm run build
```
| ID | Step | Verify | Done |
|---|---|---|---|
| PD-10 | Dependencies installed | no errors | |
| PD-11 | `.env` present with `APP_KEY` set | `php artisan key:generate --show` not empty | |
| PD-12 | Front-end assets built | `public/build/manifest.json` exists | |

## 3. Health check
```bash
php artisan test          # expect: 94 passed
php artisan config:clear
```
| ID | Step | Verify | Done |
|---|---|---|---|
| PD-20 | Test suite green | **94 passed** | |
| PD-21 | Data validation passes | See [Demo Data Validation](03-demo-data-validation-checklist.md) one-command check | |

## 4. Environment settings (`.env`)
| ID | Setting | Demo value | Notes |
|---|---|---|---|
| PD-30 | `APP_NAME` | `RAMP` | |
| PD-31 | `APP_ENV` | `local` (laptop) / `production` (hosted) | |
| PD-32 | `APP_DEBUG` | `true` local · **`false` if hosted** | Never expose stack traces publicly |
| PD-33 | `SESSION_DRIVER` | `file` | No database needed |
| PD-34 | `RAMP_DATA_PROVIDER` | `mock` | Phase-1 data source |
| PD-35 | `GOOGLE_MAPS_API_KEY` | *(your key)* | See Step 5 |

## 5. ⭐ Google Maps API key (highest risk)
| ID | Step | Done |
|---|---|---|
| PD-40 | Key present in `.env` as `GOOGLE_MAPS_API_KEY` | |
| PD-41 | **Maps JavaScript API** enabled for the key (Google Cloud Console) | |
| PD-42 | **HTTP referrer restriction includes the demo host** (`127.0.0.1`, `localhost`, and/or the hosted domain) | |
| PD-43 | Verify: open an asset → Location → map renders (not the "Map could not be loaded" fallback) | |

> If the referrer is not configured, the map shows a graceful coordinate fallback. The POC still works — but confirm before the demo, or pre-frame it. **Restrict the key by referrer + API** (it is exposed client-side by necessity).

## 6. Demo accounts (smoke test each)
| ID | Account | Password | Expected | Done |
|---|---|---|---|---|
| PD-50 | `admin@ramp.gov.in` | `password` | All data (100 assets) | |
| PD-51 | `district@ramp.gov.in` | `password` | Salem only | |
| PD-52 | `panchayat@ramp.gov.in` | `password` | Erumapalayam only; trimmed sidebar | |

## 7. Browser & presentation
| ID | Step | Done |
|---|---|---|
| PD-60 | Use latest Chrome/Edge; window maximised; 100% zoom | |
| PD-61 | Clear the tab; start signed-out at `/login` | |
| PD-62 | Disable browser notifications/popups; close unrelated tabs | |
| PD-63 | Have a phone or narrow window ready for the responsive moment | |
| PD-64 | Pre-load the **Map View** + an asset location page once (warms the Maps API) | |

## 8. Decisions to make before the demo
| ID | Decision | Recommendation | Done |
|---|---|---|---|
| PD-70 | Feature the **Asset Intelligence Map** (dashboard + Map View) | Demo it — it's the flagship; confirm the Maps key referrer first (PD-64) | |
| PD-71 | Online vs offline demo | Maps + fonts need internet; have a backup screenshot deck | |
| PD-72 | Which roles to show | Admin + Panchayat Officer (clearest contrast) | |

---

## Final 60-second pre-flight
- [ ] `php artisan serve` running; `/login` loads.
- [ ] All three accounts sign in correctly.
- [ ] One asset's Location map renders (or fallback accepted).
- [ ] Dashboard reconciles (100 assets, Salem 86 / Erode 14).
- [ ] Mobile/narrow view confirmed.

*End of Pre-Demo Configuration Checklist.*
