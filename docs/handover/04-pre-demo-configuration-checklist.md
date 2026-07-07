# Pre-Demo Configuration Checklist — RAMP POC

| Field | Value |
|---|---|
| Document | Pre-Demo Configuration Checklist |
| Audience | Developer / Presenter setting up the demo |
| Goal | A clean, fast, error-free demo environment |
| Time | ~20–30 minutes the first time; ~5 minutes thereafter |

> Complete this **before** the [Stakeholder Demo Script](01-stakeholder-demo-script.md). Maps are keyless (Leaflet + OpenStreetMap), so the main requirement is a green test suite, built assets, and internet access for the map tiles.

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
php artisan test          # expect: 110 passed
php artisan config:clear
```
| ID | Step | Verify | Done |
|---|---|---|---|
| PD-20 | Test suite green | **110 passed** | |
| PD-21 | Data validation passes | See [Demo Data Validation](03-demo-data-validation-checklist.md) one-command check | |

## 4. Environment settings (`.env`)
| ID | Setting | Demo value | Notes |
|---|---|---|---|
| PD-30 | `APP_NAME` | `RAMP` | |
| PD-31 | `APP_ENV` | `local` (laptop) / `production` (hosted) | |
| PD-32 | `APP_DEBUG` | `true` local · **`false` if hosted** | Never expose stack traces publicly |
| PD-33 | `SESSION_DRIVER` | `file` | No database needed |
| PD-34 | `RAMP_DATA_PROVIDER` | `mock` | Phase-1 data source |
| PD-35 | `SESSION_ENCRYPT` | `true` | Encrypts the session payload |

## 5. Maps (no configuration needed)
| ID | Step | Done |
|---|---|---|
| PD-40 | Maps use **Leaflet + OpenStreetMap** — **no API key, no billing, no referrer setup** | |
| PD-41 | Verify: open an asset → Location → the OSM map renders with a pin | |
| PD-42 | Verify: **Map View** (sidebar) → the Asset Intelligence Map renders with clustered markers | |

> The former Google Maps dependency (and its API-key/referrer risk) has been **removed**. Maps now load free OpenStreetMap tiles over the internet with no key — the previous "highest-risk" demo item no longer applies. The only requirement is internet access for the map tiles.

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
| PD-70 | Feature the **Asset Intelligence Map** (dashboard + Map View) | Demo it — it's the flagship; keyless OSM, just needs internet for tiles | |
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
