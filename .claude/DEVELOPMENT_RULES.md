# DEVELOPMENT_RULES.md — RAMP (Laravel 12 + Livewire 3)

> Coding standards Claude Code must follow when implementing RAMP. **Stack: Laravel 12 · PHP 8.2+ · Livewire 3 · Alpine · Tailwind v4 · ApexCharts · Google Maps. There is NO React, Vue, or TypeScript in this project.** These rules complement `LARAVEL_ARCHITECTURE.md` (structure/seam), `BUSINESS_RULES.md` (domain logic), and `UI_DESIGN_SYSTEM.md` (premium look). The original source `docs/10-claude-development-guide.md` is technology-agnostic; where it shows React/TS examples, this file is the Laravel-native authority.

---

## 1. Coding Standards

### 1.1 General

| ID | Rule |
|---|---|
| CS-01 | **Clarity over cleverness.** Code is read by future sessions (human and AI). Favor explicit, readable PHP/Blade over terse tricks. |
| CS-02 | **Single responsibility** per class/method. If a class does data access *and* computation *and* formatting, split it. |
| CS-03 | **Name by domain.** Use domain terms consistent with the model and rules: `AssetData`, `PanchayatData`, `LifecycleStatus`, `remainingLife`, `CategoryData`. |
| CS-04 | **No magic numbers** for domain thresholds. Define them once in `config/ramp.php` (e.g. `lifecycle.near_expiry_years = 5`). |
| CS-05 | **Centralize constants** (thresholds, hierarchy order, provider selection, status set) in `config/ramp.php` and the `LifecycleStatus` enum so a future change is a single edit. |
| CS-06 | **Keep functions small and pure** where possible — especially in `app/Support` and `app/Services`. |
| CS-07 | **Comment the "why," not the "what."** Document business-rule rationale (e.g. why RL == 5 is Near Expiry) at the computation site. |
| CS-08 | **Consistent formatting** — run **Laravel Pint** (`vendor/bin/pint`); do not hand-format. |
| CS-09 | **`declare(strict_types=1)`** at the top of every PHP class. |

### 1.2 Data & Types

| ID | Rule |
|---|---|
| CS-10 | **Mirror entity field names** from `docs/06`. Mock JSON uses `snake_case`; map to camelCase **once**, inside the DTO `fromArray()` at the data boundary. |
| CS-11 | **Define explicit DTOs** for every entity (`DistrictData`, `ZoneData`, `PanchayatData`, `CategoryData`, `AssetData`, `PhotoData` — District is the top level, there is no State) plus `DashboardSummary`, `Breakdown`, `HealthSummary`, and `AssetFilter`. DTOs are `final readonly` classes in `app/DataObjects`. |
| CS-12 | **Treat DTO shapes as the API contract.** Do not diverge field names or structure; the future Eloquent/API provider must return the same shapes. |
| CS-13 | The four status values are a PHP **enum** (`App\Enums\LifecycleStatus`) — the single definition of labels and canonical colors. |

### 1.3 Business Logic

| ID | Rule |
|---|---|
| CS-14 | Implement lifecycle computation **exactly** per `BUSINESS_RULES.md` in `app/Support/Lifecycle/LifecycleCalculator`, including boundaries (RL == 5 → Near Expiry; RL == 0 → Expired) and Unknown handling. |
| CS-15 | **One implementation** of lifecycle and one of aggregation, both reused everywhere (`LifecycleCalculator`, `DashboardService`). |
| CS-16 | **Unit-test boundaries and Unknown cases** (the worked-example table from `BUSINESS_RULES.md`/`docs/07`). |

> UI coding standards are in `UI_RULES.md` + `UI_DESIGN_SYSTEM.md`. Error-handling standards are in §8 below.

---

## 2. Folder Structure (Laravel)

The structure as built. Keep the layer separation.

```
app/
├── Contracts/                # THE SEAM — provider interfaces the app depends on
│   ├── AssetDataProvider.php
│   └── DashboardDataProvider.php
├── DataProviders/            # DATA LAYER — the ONLY place that reads mock JSON
│   ├── MockAssetProvider.php
│   ├── MockDashboardProvider.php
│   └── Concerns/ReadsMockJson.php
├── Services/                 # BUSINESS LOGIC — all of it
│   ├── AssetService.php
│   ├── DashboardService.php
│   └── CategoryService.php
├── Support/                  # PURE DOMAIN HELPERS (testable, no UI, no data access)
│   ├── Lifecycle/{LifecycleCalculator,LifecycleResult}.php
│   └── Filtering/AssetFilter.php
├── Enums/LifecycleStatus.php
├── DataObjects/              # readonly DTOs (entity + dashboard shapes)
├── Livewire/                 # VIEW ORCHESTRATION — one full-page component per screen
│   ├── Dashboard/...  Hierarchy/...  Assets/...
└── Providers/DataLayerServiceProvider.php   # composition root (config-driven binding)

config/ramp.php               # thresholds, hierarchy order, data_provider, mock-data disk, gmaps key
storage/app/mock-data/*.json  # the ONLY data source in Phase 1
public/mock-images/           # placeholder asset photos
resources/
├── css/app.css               # Tailwind v4 @theme tokens (UI_DESIGN_SYSTEM)
├── js/app.js                 # Alpine (via Livewire) + ApexCharts
└── views/
    ├── layouts/app.blade.php  # AppShell
    ├── components/            # reusable Blade primitives (presentation only)
    └── livewire/              # one Blade view per Livewire component
routes/web.php                 # routes → full-page Livewire components
tests/{Unit,Feature}/          # PHPUnit; NO database
```

**Folder rules:**

| ID | Rule |
|---|---|
| FS-01 | `app/Livewire` + `resources/views` may use `app/Services`, `app/Support`, `app/DataObjects`, and `app/Enums` — but must **never** read `storage/app/mock-data/*.json` directly. |
| FS-02 | `app/Support` is **pure logic**: no facades-for-data, no UI; trivially unit-testable. |
| FS-03 | `app/DataProviders` is the **only** place that knows about mock JSON (Phase 1) or Eloquent/HTTP (Phase 2+). |
| FS-04 | Blade components in `resources/views/components` are **presentation-only** and never resolve a provider or service for data. |
| FS-05 | **Livewire components orchestrate**: they receive Services via method/`boot()` injection, call them, and expose data + loading/empty/error state to their Blade view. |
| FS-06 | One full-page Livewire component per documented screen in `docs/04-screen-flow-document.md`. |

---

## 3. Naming Conventions

| Subject | Convention | Example |
|---|---|---|
| Mock JSON fields | `snake_case` (matches `docs/06`) | `asset_number`, `construction_year`, `panchayat_id` |
| Mock JSON files | lowercase plural | `assets.json`, `panchayats.json` |
| Entity IDs (mock) | human-readable, prefixed | `CAT-EDU`, `PAN-ERU`, `EDU-0001` |
| Classes (DTOs, Services, Providers) | `PascalCase` | `AssetData`, `AssetService`, `MockAssetProvider` |
| Interfaces (contracts) | `PascalCase`, role-named | `AssetDataProvider` |
| Enums | `PascalCase` type, `PascalCase` cases | `LifecycleStatus::NearExpiry` |
| Livewire components | `PascalCase` class under `app/Livewire/<Feature>/` | `Assets\AssetList` |
| Blade components | kebab-case file → `<x-...>` tag | `status-badge.blade.php` → `<x-status-badge>` |
| Methods / variables | `camelCase` | `computeLifecycle`, `remainingLife`, `assets()` |
| Config / constants | `snake_case` config keys; `UPPER_SNAKE_CASE` consts | `ramp.lifecycle.near_expiry_years`, `DEFAULT_NEAR_EXPIRY_YEARS` |
| Status values | fixed enum set | `Healthy`, `Near Expiry`, `Expired`, `Unknown` |
| Routes | named, kebab | `Route::get('/assets', AssetList::class)->name('assets')` |

**Naming rules:**

- The four **status values** are a fixed enum set used app-wide: `Healthy`, `Near Expiry`, `Expired`, `Unknown`. Do not introduce synonyms.
- Domain identifiers in code must match the documented entity field names; map `snake_case` → camelCase **once** in the DTO `fromArray()`, not ad hoc.

---

## 4. Reusability Rules

| ID | Rule |
|---|---|
| RU-01 | **Build shared primitives once, reuse everywhere** as Blade components. Never re-implement a card, table, badge, breadcrumb, or empty state per screen. |
| RU-02 | If the same markup/logic appears in **two** places, extract a Blade component or a service method. |
| RU-03 | Reusable Blade components are **configurable via props/slots**, not forked per screen. |
| RU-04 | Domain computations (status, counts, filtering) are reused from `app/Services` + `app/Support` — never copy-pasted. |
| RU-05 | Shared constants (thresholds, hierarchy order, status set) come from `config/ramp.php` + `LifecycleStatus` — never re-declared. |

**Reusable Blade components (premium specs in `UI_DESIGN_SYSTEM.md`):**

| Component | Responsibility | Used By |
|---|---|---|
| `layouts.app` | Header + breadcrumb slot + content shell | All screens |
| `<x-breadcrumb>` | Render and navigate the hierarchy path | All drill-down screens |
| `<x-card>` | Generic surface / drill-down card | Dashboard, hierarchy, detail |
| `<x-kpi-card>` | Dashboard metric card | Dashboard |
| `<x-data-table>` | Columnar list; collapses to cards on narrow widths | Asset List |
| `<x-status-badge>` | Computed status pill (canonical color + label) | Lists, detail, lifecycle, dashboard |
| `<x-empty-state>` | Standard empty / no-results presentation | Lists, gallery, location |
| `<x-filter-bar>` *(Sprint 3)* | Search + filters + chips + reset | Asset List |
| `<x-photo-thumb>` / `<x-lightbox>` *(Sprint 2)* | Thumbnail + enlarged view + placeholder | Photo Gallery, Asset Detail |
| `<x-map-panel>` *(Sprint 2)* | Google Maps pin + "unavailable" state | Location View, Asset Detail |
| `<x-lifecycle-panel>` *(Sprint 2)* | Figures + status (+ life-consumed bar) | Asset Detail, Lifecycle View |

---

## 5. Component Rules (Blade + Livewire)

| ID | Rule |
|---|---|
| CR-01 | **Blade components receive data via props/slots**; they do **not** fetch or compute business logic. |
| CR-02 | Keep Blade components **presentation-focused**. Local UI-only interactivity (lightbox open, dropdown) uses **Alpine** (`x-data`), not server round-trips. |
| CR-03 | `<x-status-badge>` takes a `LifecycleStatus` produced by the lifecycle service — it **never computes status**. |
| CR-04 | Every list/grid/gallery/map renders its **empty state** via `<x-empty-state>`. |
| CR-05 | Components are **provider-agnostic** — unaware of mock vs Eloquent. |
| CR-06 | Blade components never resolve `app/DataProviders` or read JSON. |
| CR-07 | Components handle **missing optional fields** gracefully (no crash on absent address/coordinates/photos). |
| CR-08 | Follow `UI_RULES.md` + `UI_DESIGN_SYSTEM.md` for visual standards (status colors, card/table anatomy, spacing, motion, responsiveness). |

---

## 6. Page (Screen) Rules — Livewire full-page components

| ID | Rule |
|---|---|
| PG-01 | One full-page Livewire component per documented screen in `docs/04` (Dashboard, ZoneList, PanchayatList, CategoryList, AssetList, AssetDetail, PhotoGallery, LocationView, LifecycleView), each with `#[Layout('layouts.app')]`. |
| PG-02 | Livewire components **orchestrate**: inject Services, obtain data, pass results to Blade. They do not read JSON or compute lifecycle/counts inline. |
| PG-03 | Carry drill-down / filter context with `#[Url]` public properties + `wire:navigate` links — never session hacks or hard-coding per screen. |
| PG-04 | Every screen below the Dashboard renders a **breadcrumb** and supports upward navigation. |
| PG-05 | Screens render **loading, empty, and error** states (Livewire `wire:loading`, `<x-empty-state>`) — never a frozen or blank screen. |
| PG-06 | The **Asset List** screen renders identically regardless of entry path (drill-down, dashboard shortcut, or search). |
| PG-07 | Drill-down screens **carry and pass context** (selected zone/panchayat/category/status) to their destination. |
| PG-08 | Sub-view screens (Photos, Location, Lifecycle) **return to** their parent Asset Detail. |

---

## 7. Mock Data Usage Rules

| ID | Rule |
|---|---|
| MD-01 | **Mock JSON is the only data source in Phase 1.** It lives in `storage/app/mock-data/*.json` and is read **only** by `ReadsMockJson` inside `app/DataProviders`. |
| MD-02 | Mock data shapes **conform to `docs/06`** and are authored per `docs/08`. Treat these shapes as the future API contract. |
| MD-03 | **Lifecycle status is NOT in mock data.** Only `construction_year` and `expected_life` are stored; status is computed at runtime. |
| MD-04 | Mock data includes **status variety** (Healthy, Near Expiry, Expired, and ≥1 Unknown). |
| MD-05 | Maintain **referential integrity**: every `Asset` references a valid `panchayat_id`/`category_id`; every hierarchy node a valid parent; every `Photo` a valid asset. |
| MD-06 | `asset_number` is **unique** across all assets. |
| MD-07 | `asset_type` must be a **valid sub-type** of the asset's category. |
| MD-08 | **No Livewire component, Blade view, or Service imports JSON directly.** All access is via the `AssetDataProvider` / `DashboardDataProvider` contracts. |
| MD-09 | **No hard-coded counts.** All counts come from `DashboardService`/`CategoryService` over the live mock data. |
| MD-10 | Placeholder photos live in `public/mock-images/`; broken/missing image sources render a placeholder (BR-PH-03). |

---

## 8. Error Handling Rules

| ID | Rule |
|---|---|
| EH-01 | The data providers return **predictable empties** (`[]`, `null`) for "not found" rather than throwing. |
| EH-02 | Components render **empty states** for empties; they never crash on missing optional fields. |
| EH-03 | **Validate at the boundary.** Validate lifecycle inputs and coordinates where data enters the domain/UI; degrade gracefully (Unknown status, "location unavailable"). |
| EH-04 | **No dead-ends or raw errors in the UI.** A level with no records shows an empty state and a way back (BR-NV-08). |
| EH-05 | **Invalid/unknown identifiers** redirect to the nearest valid ancestor with an informational message (BR-NV-09). |
| EH-06 | **Lifecycle edge cases** resolve to **Unknown** (missing/invalid inputs, future construction year, non-positive expected life) — never a crash. |
| EH-07 | **Photos:** missing/broken image → placeholder; no photos → empty state. |
| EH-08 | **Location:** missing/invalid coordinates → "location unavailable" while still showing the rest of the record and the address if present. |
| EH-09 | **Search/filter:** no matches → clear "no results" empty state with a reset — not an error. |
| EH-10 | Design error handling so **future API failures** surface through the same `wire:loading`/empty/error states — no redesign in Phase 2. |

---

## 9. Testing Standards (PHPUnit — no database)

| ID | Rule |
|---|---|
| TS-01 | **Unit-test `LifecycleCalculator`** against the boundary table (RL = 14 Healthy, RL = 5 Near Expiry, RL = −1 Expired, RL = 0 Expired, missing inputs → Unknown). |
| TS-02 | **Test `DashboardService`** counts (total, by category, by zone, by panchayat, by status) against the seed expectations in `docs/08 §9`. |
| TS-03 | **Test filtering/search** via `AssetService`: zone, panchayat, category, status, query — AND across filters, case-insensitive substring search. |
| TS-04 | Keep tests **provider-agnostic** where possible so they also validate a future `EloquentAssetProvider` against the same contract. |
| TS-05 | Assert **categorization reconciliation** (sum of per-category counts == total) and that **Unknown** is excluded from health percentages but counted separately. |
| TS-06 | The suite runs with **no database** (file/array drivers). Do not add `RefreshDatabase` or migrations. |

---

## 10. Definition of Done (Per Feature)

A feature is done when:

- ✅ It follows the **layering and separation** rules (no JSON in UI, no inline domain logic).
- ✅ It uses **shared** services and reusable Blade components.
- ✅ It implements **loading, empty, and error** states.
- ✅ It matches the relevant **acceptance criteria** in `docs/03`.
- ✅ It honors the relevant **business rules** in `BUSINESS_RULES.md`.
- ✅ It follows **UI standards** in `UI_RULES.md` + `UI_DESIGN_SYSTEM.md` (status colors, premium look, breadcrumbs, responsiveness).
- ✅ Relevant **PHPUnit tests** pass (lifecycle/aggregation/filtering as applicable).
- ✅ Swapping the mock provider for an Eloquent provider would require **no change** to this feature's UI or Service.

---

*End of DEVELOPMENT_RULES.md (Laravel 12 + Livewire 3)*
