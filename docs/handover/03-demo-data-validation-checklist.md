# Demo Data Validation Checklist — RAMP POC

| Field | Value |
|---|---|
| Document | Demo Data Validation Checklist |
| Audience | QA, Developer |
| Data source | `storage/app/mock-data/*.json` (read only by the data providers) |
| Purpose | Confirm the mock dataset is internally consistent and demo-safe |

> The dataset is the only Phase-1 data source. These checks confirm referential integrity, status variety, and that every referenced image exists — so nothing breaks or looks wrong during the demo.

---

## A. Collections present
| ID | Check | Expected | P/F |
|---|---|---|---|
| DV-01 | `districts.json` | 2 districts (Salem, Erode) | |
| DV-02 | `zones.json` | 5 zones | |
| DV-03 | `panchayats.json` | 13 panchayats | |
| DV-04 | `categories.json` | **10 categories** (full OHT/UGT labels) | |
| DV-05 | `assets.json` | **100 assets** | |
| DV-06 | `users.json` | 3 users (admin / district / panchayat) | |
| DV-07 | No `states.json` | Hierarchy starts at District | |

## B. Referential integrity
| ID | Check | Expected | P/F |
|---|---|---|---|
| DV-10 | Every asset `panchayat_id` resolves | Valid panchayat | |
| DV-11 | Every asset `zone_id` / `district_id` resolves | Valid + consistent with panchayat | |
| DV-12 | Every asset `category_id` resolves | One of the 10 categories | |
| DV-13 | `asset_type` is a valid sub-type of its category | Yes | |
| DV-14 | `asset_number` unique across all assets | No duplicates | |
| DV-15 | Every photo `url` exists under `public/asset-images/` | File present | |

## C. Lifecycle / status variety (so all UI states are demoable)
| ID | Check | Expected | P/F |
|---|---|---|---|
| DV-20 | At least one **Healthy** asset | Yes (majority) | |
| DV-21 | At least one **Near Expiry** asset | Yes | |
| DV-22 | At least one **Expired** asset | Yes | |
| DV-23 | At least one **Unknown** asset (null construction year) | Yes (1 — Jagirammapalayam Community Function Hall) | |
| DV-24 | At least one asset with **no photos** | Yes | |
| DV-25 | At least one asset with **no coordinates** | Yes (the Unknown asset) | |
| DV-26 | Expected life is **25 for all** (no per-asset values) | Yes (field absent; fixed in config) | |

## D. Reconciliation (counts derive from data)
| ID | Check | Expected | P/F |
|---|---|---|---|
| DV-30 | Sum of district asset counts = 100 | Salem (86) + Erode (14) = 100 | |
| DV-31 | Sum of category counts = 100 | Across all 10 categories | |
| DV-32 | Health total = 100 | Healthy + Near + Expired + Unknown | |
| DV-33 | Veerapandi Panchayat is empty | Demonstrates zero-count categories | |

## E. Demo-safety
| ID | Check | Expected | P/F |
|---|---|---|---|
| DV-40 | No offensive / placeholder-looking names | Realistic Tamil Nadu names | |
| DV-41 | Coordinates land on real localities | Salem/Erode area | |
| DV-42 | Category photos are appropriate (TN govt buildings) | Yes | |
| DV-43 | No real personal data in `users.json` | Demo emails only (`@ramp.gov.in`) | |

---

## One-command verification
Run from the project root. (Read-only; touches no files.)

```bash
php artisan tinker --execute="
\$p = app(App\Contracts\AssetDataProvider::class);
\$d = app(App\Services\DashboardService::class)->summary();
\$zones = collect(\$p->zones())->keyBy('id'); \$pans = collect(\$p->panchayats())->keyBy('id'); \$cats = collect(\$p->categories())->keyBy('id');
\$nums = []; \$err = 0; \$missImg = 0;
foreach (\$p->assets() as \$a) {
  if (!\$pans->has(\$a->panchayatId)) \$err++;
  if (\$a->zoneId && !\$zones->has(\$a->zoneId)) \$err++;
  \$c = \$cats->get(\$a->categoryId); if (!\$c) \$err++;
  elseif (!in_array(\$a->assetType, \$c->subTypes, true)) \$err++;
  if (isset(\$nums[\$a->assetNumber])) \$err++; \$nums[\$a->assetNumber] = 1;
  foreach (\$a->photos as \$ph) { if (!file_exists(public_path(ltrim(\$ph->url,'/')))) \$missImg++; }
}
echo 'assets='.\$d->totalAssets.' categories='.\$d->totalCategories.' zones='.\$d->totalZones.' panchayats='.\$d->totalPanchayats.PHP_EOL;
echo 'health H/N/E/U='.\$d->health->healthy.'/'.\$d->health->nearExpiry.'/'.\$d->health->expired.'/'.\$d->health->unknown.PHP_EOL;
echo 'integrity_errors='.\$err.' missing_images='.\$missImg.PHP_EOL;
"
```
**Expected:** `assets=100 categories=10 zones=5 panchayats=13`, `integrity_errors=0`, `missing_images=0`.

> The automated suite also enforces these (see `tests/Feature/AggregationTest.php`, `AssetFilterTest.php`, `CategoryDashboardTest.php`). Run `php artisan test` → all green.

*End of Demo Data Validation Checklist.*
