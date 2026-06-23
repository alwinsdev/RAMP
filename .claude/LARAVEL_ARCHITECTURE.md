# LARAVEL_ARCHITECTURE.md — RAMP (Laravel 12 + Livewire 3)

> **Stack-specific governing document.** RAMP is built with **Laravel 12 + Livewire 3 + Alpine + Tailwind v4**, charts via **ApexCharts**, maps via **Google Maps JS API**. This file is the authority for *how* the POC is structured in Laravel. It **supersedes the React-shaped examples** (`.ts/.tsx`, hooks, `screens/`, `dataServiceFactory`) in `ARCHITECTURE_RULES.md` / `DEVELOPMENT_RULES.md` / `10-claude-development-guide.md` wherever they describe *implementation form*.
>
> **Domain rules are unchanged and still authoritative:** `BUSINESS_RULES.md` / `docs/07` (lifecycle, navigation, search/filter, integrity) and `UI_RULES.md` / `docs/11` (status colors, layout, responsive). When a stack example here conflicts with a **domain rule**, the domain rule wins.

---

## 1. Technology Stack

| Concern | Choice |
|---|---|
| Backend | Laravel 12 (PHP 8.2+; target 8.4) |
| View / interactivity | Blade + Livewire 3 (Alpine ships with Livewire) |
| Styling | Tailwind CSS v4 (CSS-config via `@theme` in `resources/css/app.css`) |
| Charts | ApexCharts (dashboard health, lifecycle "life consumed") |
| Maps | Google Maps JavaScript API (Location View) |
| Data source (Phase 1) | Mock JSON in `storage/app/mock-data/` |
| Tests | PHPUnit 11 (`php artisan test`) — no database |

**Phase-1 hard constraints (unchanged):** no database, no migrations, no Eloquent, no auth, no CRUD/write UI, no API development, no excluded modules.

---

## 2. The Architecture (current vs future)

```
Phase 1 (now):
  Livewire component → Service → Contract (seam) → Mock Provider → storage/app/mock-data/*.json

Phase 2+ (future):
  Livewire component → Service → Contract (seam) → Eloquent Provider → Repository → Eloquent → PostgreSQL
```

The **Livewire components, Blade views, and Services never change** across phases. Only the concrete behind the contract changes, selected by `config('ramp.data_provider')` in `DataLayerServiceProvider`. That is the migration acceptance test: implement `EloquentAssetProvider`, flip the config, verify zero UI/Service edits.

**Layer responsibilities**

| Layer | Location | Does | Must NOT |
|---|---|---|---|
| Presentation | `resources/views/**` (Blade), `app/Livewire/**` | render; hold view state; orchestrate via injected Services | read JSON; compute status/counts |
| Domain / logic | `app/Services/**`, `app/Support/Lifecycle/**`, `app/Support/Filtering/**` | all business logic (lifecycle, aggregation, search/filter) | touch JSON/HTTP/Eloquent |
| Contracts (seam) | `app/Contracts/**` | define stable provider interfaces | contain logic |
| Data providers | `app/DataProviders/**` | read mock JSON, map rows → DTOs | compute status/counts |
| DTOs | `app/DataObjects/**` | typed, readonly data shapes (= the contract) | hold derived status in data |
| Composition root | `app/Providers/DataLayerServiceProvider.php` | bind contracts→providers + LifecycleCalculator by config | be bypassed elsewhere |

---

## 3. Folder Structure (as built in Sprint 0)

```
app/
├── Contracts/                AssetDataProvider, DashboardDataProvider          (the seam)
├── DataProviders/            MockAssetProvider, MockDashboardProvider
│   └── Concerns/             ReadsMockJson                                     (ONLY place JSON is read)
├── Services/                 AssetService, DashboardService, CategoryService   (ALL business logic)
├── Support/
│   ├── Lifecycle/            LifecycleCalculator (the one engine), LifecycleResult
│   └── Filtering/            AssetFilter
├── Enums/                    LifecycleStatus (labels + canonical colors)
├── DataObjects/              DistrictData, ZoneData, PanchayatData,
│                             CategoryData, AssetData, PhotoData,
│                             Breakdown, HealthSummary, DashboardSummary
├── Livewire/                 Home (Sprint 0 landing → Dashboard in Sprint 3)
└── Providers/                DataLayerServiceProvider                          (composition root)

config/ramp.php               data_provider, lifecycle threshold, hierarchy order, mock_data disk, gmaps key
config/filesystems.php        + 'mock-data' disk → storage/app/mock-data
storage/app/mock-data/*.json  districts, zones, panchayats, categories, assets (embedded photos)
resources/
├── css/app.css               Tailwind v4 + @theme status/neutral tokens
├── js/app.js                 ApexCharts registered globally for Alpine
└── views/
    ├── layouts/app.blade.php  AppShell (header + breadcrumb slot + content)
    ├── components/            status-badge, breadcrumb, card, kpi-card, data-table, empty-state
    └── livewire/home.blade.php
tests/
├── Unit/LifecycleCalculatorTest.php          (17 cases — boundary table)
└── Feature/AggregationTest, AssetFilterTest, HomePageTest
routes/web.php                / → Home (full-page Livewire)
```

---

## 4. Conventions (Laravel-specific rules)

1. **Inject Services into Livewire** via `render()`/`mount()`/`boot()` method injection — never `new`, never resolve a provider or JSON in a component.
2. **The seam is bound in one place** (`DataLayerServiceProvider`), keyed on `config('ramp.data_provider')`. Adding `'eloquent'` is a new entry in the `PROVIDERS` map.
3. **Only `ReadsMockJson` touches the mock files.** A grep for `mock-data` outside `app/DataProviders/` is a defect (TD-01).
4. **`LifecycleStatus` is a PHP enum** — the four labels + canonical hex colors live here once (UI_RULES §3.1). No synonyms, no status color anywhere else.
5. **DTOs are `final readonly`**, built via `fromArray()` mapping `snake_case` JSON → camelCase once at the data boundary. Treated as the API contract; shapes must not drift.
6. **Status is computed, never stored.** `AssetData` carries only raw inputs; `AssetService` attaches a `LifecycleResult` via `withLifecycle()` using the single `LifecycleCalculator`. The status filter compares the computed value.
7. **Aggregation lives only in `DashboardService`**; counts derive from the live dataset (BR-DI-05). `MockDashboardProvider` is a thin data read — it does no counting.
8. **Reusable UI = Blade components** (`<x-status-badge>`, `<x-card>`, …) — build once, reuse; presentation-only, props-driven, handle their own empty state.
9. **Predictable empties:** providers return `[]`/`null` for "not found" (never throw); components render `<x-empty-state>`.
10. **No database in tests:** the suite runs with file/array drivers; do not add `RefreshDatabase`.
11. **Config over magic numbers:** threshold, hierarchy order, provider, gmaps key all in `config/ramp.php`.
12. **Drill-down / filter context** will be carried by Livewire `#[Url]` props + `wire:navigate` (Sprint 1+), so the Asset List is shareable and reversible — never session hacks.

---

## 5. Sprint 0 — Status: COMPLETE ✅

Built and verified (36 tests passing, no database):

- Laravel 12.62 scaffolded at repo root; Livewire 3, Tailwind v4, Alpine, ApexCharts installed; default migrations/SQLite removed; session/cache/queue on file/sync.
- `config/ramp.php` + dedicated `mock-data` filesystem disk.
- Seed mock data (8 assets per `docs/08`) with full status variety incl. Unknown, a no-coordinates asset, and assets with/without photos.
- `LifecycleStatus` enum + `LifecycleCalculator` + `LifecycleResult` — **17 unit tests** cover the boundary table (RL 14/5/−1/0, Unknown, future year, non-positive life).
- DTOs + `AssetDataProvider`/`DashboardDataProvider` contracts.
- `MockAssetProvider`/`MockDashboardProvider` + `ReadsMockJson` trait.
- `DataLayerServiceProvider` (config-driven binding) registered.
- `AssetService` (list/detail/filter/search), `CategoryService` (counts), `DashboardService` (KPIs/breakdowns/health) + `AssetFilter` — **aggregation + filter Feature tests** validate `docs/08 §9` reconciliation and BR-SR-*/BR-FL-* semantics.
- AppShell layout + reusable primitives (status-badge, breadcrumb, card, kpi-card, data-table, empty-state).
- `/` renders a foundation landing pulling live figures through the full chain (smoke test passing).

**Exit criteria met:** providers return all entities; lifecycle passes boundary unit tests; aggregation reconciles to the seed; app shell renders end to end.

---

## 6. What's next (not built yet)

- **Sprint 1:** Hierarchy screens (ZoneList/PanchayatList/CategoryList), Asset List table + status badges, breadcrumb wiring, `#[Url]` drill-down context.
- **Sprint 2:** Asset Detail (5 groups), Photo Gallery, Location View (Google Maps), Lifecycle View (ApexCharts).
- **Sprint 3:** Real Dashboard (replaces `Home`) with ApexCharts health distribution + drill-down shortcuts; Search/Filter bar with chips + reset.
- **Sprint 4:** Responsive table→cards, loading/empty/error polish, QA vs acceptance criteria, demo script.

---

*End of LARAVEL_ARCHITECTURE.md*
