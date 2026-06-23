# ARCHITECTURE_RULES.md — RAMP (Laravel 12 + Livewire 3)

> The architecture rules Claude Code must follow. **Stack: Laravel 12 · PHP 8.2+ · Livewire 3 · Alpine · Tailwind v4. No React, Vue, or TypeScript.** The **single most important goal** of this POC is that it migrates from mock JSON to Eloquent + PostgreSQL **without major refactoring**. These rules guarantee that. For the concrete folder layout and conventions see `LARAVEL_ARCHITECTURE.md`.

**The architectural promise:**

```
Phase 1 (now):     Livewire → Services → Contracts → MockAssetProvider     → storage/app/mock-data/*.json
Phase 2 (future):  Livewire → Services → Contracts → Api/EloquentProvider  → REST API / Eloquent
Phase 3 (future):  Livewire → Services → Contracts → EloquentProvider      → Repository → Eloquent → PostgreSQL
```

The **Livewire components, Blade views, and Services never change** across these phases. Only the **provider behind the contract** changes, selected by `config('ramp.data_provider')`.

---

## 1. Architecture Principles

1. **Depend on contracts, not implementations.** Livewire/Services depend on the **`AssetDataProvider` / `DashboardDataProvider` interfaces**, never on a concrete provider, JSON file, or HTTP client.
2. **One seam for data.** There is exactly one boundary where data enters the app — `app/DataProviders`. Swapping what's behind it (mock → Eloquent) must not ripple outward.
3. **Pure, centralized business logic.** Lifecycle and aggregation live in `app/Support` + `app/Services`, independent of the data source, reused everywhere.
4. **Presentation is dumb.** Blade views render what they're given; Livewire components orchestrate. Neither fetches JSON nor computes domain logic.
5. **Stable shapes.** The DTOs returned by the data layer match `docs/06`/`docs/08`. These shapes are the **API contract** and must not drift between phases.
6. **Configuration-driven wiring.** The active provider is chosen in **one** place — `DataLayerServiceProvider`, via `config/ramp.php`.
7. **Future-ready, not future-built.** Build only the POC, but never make a choice that blocks the Eloquent/PostgreSQL migration. Do not over-engineer into a production ERP.
8. **Migration is provable.** Success = implementing an Eloquent provider and switching the config requires **zero changes** to any Livewire component, Blade view, or Service.

---

## 2. Layering (The Layered Architecture)

```
┌─────────────────────────────────────────────────────────────┐
│  PRESENTATION   resources/views/** (Blade) + app/Livewire/** │  renders UI; no JSON, no domain math
│                 Alpine for local UI state; ApexCharts; Maps  │
├─────────────────────────────────────────────────────────────┤
│  DOMAIN         app/Services/* + app/Support/*               │  pure business logic (status, counts, filter)
│                 AssetService, DashboardService, Lifecycle... │  no UI, no data fetching
├─────────────────────────────────────────────────────────────┤
│  CONTRACT       app/Contracts/* (the interfaces = contract)  │  the stable seam the app depends on
│     ├── MockAssetProvider      (Phase 1) reads mock JSON     │
│     ├── EloquentAssetProvider  (Phase 2+) reads DB           │
│     └── DataLayerServiceProvider selects the active provider │
└─────────────────────────────────────────────────────────────┘
```

**Allowed dependency directions (downward only):**

- `app/Livewire/*` → Blade, `app/Services`, `app/Support`, `app/DataObjects`, `app/Enums` — and the data layer **via the contracts**.
- `app/Services/*` → `app/Support`, `app/DataObjects`, and the data layer **via the contracts**.
- `app/Support/*` → nothing app-specific (pure logic; may use `app/Enums`, `app/DataObjects`).
- `app/DataProviders/*` → mock JSON (Phase 1) or Eloquent/HTTP (Phase 2+) — **only here**.

**Forbidden:**

- A Livewire component or Blade view importing a concrete provider or reading `storage/app/mock-data/*.json` directly.
- `app/Support` or `app/Services` performing data fetching from disk/HTTP/Eloquent.
- Any layer reaching "around" the contract to a concrete provider.

---

## 3. Separation Of Concerns (Enforced)

| Concern | Lives In | Must NOT |
|---|---|---|
| Data fetching / source access | `app/DataProviders` | …happen in Livewire, Blade, or Services |
| Business logic (status, counts, filter) | `app/Services` + `app/Support` | …be duplicated inline in views/components |
| Orchestration / view state | `app/Livewire` (component classes) | …contain raw data access or status/count math |
| Presentation / rendering | `resources/views/**` | …read JSON or compute domain logic |
| Navigation / context | routes + Livewire `#[Url]` props | …be hard-coded per screen |
| Provider selection / wiring | `DataLayerServiceProvider` | …be decided inside a Livewire component |

**The one-question self-check:**

> *If I swapped `MockAssetProvider` for `EloquentAssetProvider`, would I have to edit any Livewire component, Blade view, or Service?*
> If **yes**, the separation is broken — fix the abstraction, not the screen.

---

## 4. Service Layer Rules

The seam is the heart of future-readiness.

### 4.1 The Contracts (PHP interfaces)

Interfaces describe **what** data the app needs, not **how** it's fetched. Method names and return shapes match `docs/06`/`docs/08`.

```php
interface AssetDataProvider
{
    /** @return DistrictData[] */  public function districts(): array;   // District is the top level (no State)
    /** @return ZoneData[] */      public function zones(?string $districtId = null): array;
    /** @return PanchayatData[] */ public function panchayats(?string $zoneId = null): array;
    /** @return CategoryData[] */  public function categories(): array;
    /** @return AssetData[] */     public function assets(): array;          // raw — no lifecycle attached
    public function assetById(string $assetId): ?AssetData;
    /** @return PhotoData[] */     public function photosByAsset(string $assetId): array;
}

interface DashboardDataProvider
{
    /** @return AssetData[] */     public function allAssets(): array;
    /** @return ZoneData[] */      public function zones(): array;
    /** @return PanchayatData[] */ public function panchayats(): array;
    /** @return CategoryData[] */  public function categories(): array;
}
```

### 4.2 Rules

| ID | Rule |
|---|---|
| SL-01 | Livewire/Services depend **only** on the contracts — never on a concrete provider. |
| SL-02 | `MockAssetProvider implements AssetDataProvider` (Phase 1): loads and maps `storage/app/mock-data/*.json` in memory. |
| SL-03 | `EloquentAssetProvider implements AssetDataProvider` (Phase 2+): reads the DB and returns the **same DTO shapes**. |
| SL-04 | `DataLayerServiceProvider` binds contracts → concretes via `config('ramp.data_provider')`. Swapping providers requires **no UI/Service change**. |
| SL-05 | Providers return **predictable empties** (`[]`, `null`) for "not found" — they do not throw for missing data. |
| SL-06 | DTO shapes are **stable** across providers; field names match the documented entities and must not drift. |
| SL-07 | Filtering/search semantics (AND across filters, OR within a multi-value filter, case-insensitive substring search) live in the **service layer** so they are identical for every provider. |
| SL-08 | The data layer is the **only** place that knows about mock JSON (now) or Eloquent/HTTP (future). |
| SL-09 | Keep method signatures simple value-returning so a future provider (even one calling an API) fits without changing call sites. |
| SL-10 | The provider does **not** compute lifecycle status; it returns raw inputs and lets `LifecycleCalculator` (via `AssetService`) derive status. |

### 4.3 Domain Services (shared, pure)

| ID | Rule |
|---|---|
| DS-01 | **Lifecycle:** `LifecycleCalculator` computes `currentAge`, `remainingLife`, and `status` per `BUSINESS_RULES.md`. Every consumer uses it — no inline duplication. |
| DS-02 | **Aggregation:** `DashboardService` computes dashboard counts (total, by zone, by panchayat, by category, by status) from the asset set. |
| DS-03 | Domain code in `app/Support` is **pure** (no UI, no fetching) and **unit-tested**, especially lifecycle boundaries and aggregation totals. |
| DS-04 | Thresholds, hierarchy order, and the status set are **centralized** (`config/ramp.php`, `LifecycleStatus` enum) so a future change is a one-line edit. |

> If you find yourself computing status or counts inside a Livewire component or Blade view, stop and move it into `app/Services`/`app/Support`.

---

## 5. Component Design Rules (Blade + Livewire)

| ID | Rule |
|---|---|
| CO-01 | Blade components are **presentation-focused** and receive everything via props/slots; they do **not** fetch data or compute business logic. |
| CO-02 | Lift shared state to Livewire component classes. Local UI-only state (lightbox open, dropdown) uses **Alpine** inside the Blade. |
| CO-03 | `<x-status-badge>` renders a `LifecycleStatus` **produced by the lifecycle service** — it never computes status itself. |
| CO-04 | Build **reusable Blade primitives once** and reuse across screens (see `DEVELOPMENT_RULES.md` §4). |
| CO-05 | Every list/grid/gallery/map handles its **empty state** via `<x-empty-state>`. |
| CO-06 | Components are **provider-agnostic** — unaware of mock vs Eloquent. |
| CO-07 | Blade components do not resolve `app/DataProviders`. They receive already-fetched, already-computed data from Livewire. |

---

## 6. State Management Rules (Livewire)

The POC is **read-only**, so state is mostly fetched data + UI/navigation state. Keep it minimal.

| ID | Rule |
|---|---|
| ST-01 | **No global store needed.** Livewire components fetch through injected Services and pass results to Blade. |
| ST-02 | **View state** (selected filters, search query, "is gallery open") lives in the Livewire component (or Alpine for pure-UI bits) — co-located with the screen. |
| ST-03 | **Navigation context** (accumulated zone/panchayat/category/status) is carried via Livewire `#[Url]` public properties + `wire:navigate` — never hard-coded per screen. |
| ST-04 | **Derived values are never stored.** Lifecycle status and counts are computed by services on demand, not persisted into component state as source-of-truth. |
| ST-05 | Any caching wraps the **service/contract** (not direct JSON/HTTP), so the future provider swap stays transparent. |
| ST-06 | **Do not use browser/session storage as a datastore.** The Phase-1 source is the mock provider only. |
| ST-07 | Loading/empty/error are **explicit states** (Livewire `wire:loading`, `<x-empty-state>`) so future API latency/failures are already handled. |

---

## 7. Future API/DB Integration Rules (Phase 2)

| ID | Rule |
|---|---|
| API-01 | **Do not touch Livewire, Blade, or Services.** Implement the new provider against the **same** contracts. |
| API-02 | Map responses to the **documented DTO shapes** (`docs/06`/`docs/08`). Map at the data layer if the backend differs. |
| API-03 | Honor the **same observable behavior** for search/filter (AND/OR semantics, case-insensitive substring). |
| API-04 | Keep method signatures stable so call sites don't change. |
| API-05 | Switch the active provider in `DataLayerServiceProvider` via **config** only. |
| API-06 | Preserve **predictable empties** (`[]`, `null`) and graceful error surfacing so existing empty/error states keep working. |
| API-07 | **Verify zero UI/Service changes** were required. That verification is the Phase-2 acceptance test. |
| API-08 | Lifecycle status may move server-side, but the **client contract is identical** — the UI still receives the same status values. |

---

## 8. Future Database Migration Rules (Phase 3)

| ID | Rule |
|---|---|
| DB-01 | Each **logical entity** maps to **one Eloquent model / table** and **one API resource** — the mock collections were designed for this 1:1 mapping (`docs/06`). |
| DB-02 | Stable string `id`s used as foreign keys in mock data map directly to **primary/foreign keys**. Relationships are unchanged. |
| DB-03 | **Lifecycle status remains derived** — the database stores only inputs (`construction_year`, `expected_life`). |
| DB-04 | The database is reached **only** through the Eloquent provider behind the contract. UI and Services are untouched. |
| DB-05 | **Write operations** (create/edit/delete) are added behind the same contract as **new methods**. Existing read paths do not change. |
| DB-06 | **No hard-coded counts** survive — all totals derive from queries/aggregations, exactly as `DashboardService` does over mock data. |
| DB-07 | Validation rules (unique `asset_number`, valid parent references, valid coordinates, valid lifecycle inputs) become **DB constraints / form requests**, mirroring the runtime checks the POC applies. |
| DB-08 | The migration is **incremental and low-risk** because the seam (contracts) and DTO shapes were fixed in Phase 1. |

---

## 9. Anti-Patterns (Reject These Immediately)

- ❌ A Livewire component or Blade view resolving a concrete provider or reading `storage/app/mock-data/*.json`.
- ❌ Computing lifecycle status or dashboard counts inside a component/view instead of `app/Services`/`app/Support`.
- ❌ Storing computed lifecycle status (in JSON, state, or DB) as the source of truth.
- ❌ Hard-coded totals/counts anywhere.
- ❌ Provider selection scattered across screens instead of in `DataLayerServiceProvider`.
- ❌ Diverging DTO shapes between providers (mock vs Eloquent) — breaks the contract.
- ❌ Using browser/session storage as a datastore.
- ❌ Reintroducing a **React/Vue/SPA** frontend or a JS build of the UI — the UI is **Blade + Livewire + Alpine** only.
- ❌ Over-engineering production concerns (RBAC, audit, write workflows) into the POC.

---

## 10. Acceptance Test for "Future-Ready"

The architecture is correct **iff** all of these are true:

1. ✅ Searching the codebase for `mock-data` finds it **only** inside `app/DataProviders`.
2. ✅ There is **exactly one** lifecycle computation (`LifecycleCalculator`) and **one** aggregation (`DashboardService`).
3. ✅ Livewire/Services depend on the **contracts**, never a concrete provider.
4. ✅ Provider selection happens in **one** place (`DataLayerServiceProvider`) via config.
5. ✅ DTO shapes match `docs/06`/`docs/08` and are identical across providers.
6. ✅ A hypothetical `EloquentAssetProvider` could replace `MockAssetProvider` by editing **only** the config/binding.

---

*End of ARCHITECTURE_RULES.md (Laravel 12 + Livewire 3)*
