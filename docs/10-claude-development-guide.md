# Claude Development Guide — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-10 |
| Document Title | Claude Development Guide |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Future Claude (AI) sessions, Developers |
| Related Documents | 03-FRS, 06-Data Model, 07-Business Rules, 08-Mock Data, 11-UI/UX |

---

## 1. Purpose

This document instructs any future development session (human or Claude) on **how** to build the RAMP POC so it stays consistent, correct, and future-ready. Read this **before writing code**. It encodes the non-negotiable constraints, the architecture that makes future migration painless, the folder structure, reusable component expectations, and coding standards.

> **Golden rule:** Build the POC as if the database and APIs already exist — only the *implementation* behind the data layer is mocked. Never let the UI know it is talking to mock JSON.

---

## 2. Non-Negotiable Constraints (Read First)

1. **Use mock JSON only.** All data comes from `mock-data/*.json` (see `08-mock-data-specification.md`). No database, no ORM, no persistence in Phase 1.
2. **No database implementation.** Do not add SQLite/Postgres/Mongo or any storage engine.
3. **No UI write operations** (create/edit/delete) in Phase 1. The POC reads and displays.
4. **Stay future-ready.** Every data access goes through a single abstraction so the mock provider can be swapped for an API client later with zero UI changes.
5. **Do not build excluded modules:** maintenance, inspections, work orders, notifications, approvals, native mobile.
6. **Lifecycle status is always computed**, never stored or hard-coded (see `07-business-rules.md`).
7. **Do not over-engineer** as a production ERP — but never make a choice that blocks production later.

---

## 3. Architecture: The Future-Ready Pattern

The core of future-readiness is a **data/service layer** that the UI depends on through a stable interface. In Phase 1 it is implemented by a mock provider; in Phase 2 it is reimplemented by an API client. The UI is unaware of which is active.

### 3.1 Layering

```
┌─────────────────────────────────────────────┐
│  UI / Screens (components)                   │  ← never reads JSON or computes status inline
├─────────────────────────────────────────────┤
│  View Logic / Hooks / Controllers            │  ← orchestrates calls to services
├─────────────────────────────────────────────┤
│  Domain Services (lifecycle, aggregation)    │  ← shared business logic (status, counts)
├─────────────────────────────────────────────┤
│  Data Service Interface (the contract)       │  ← stable methods the app depends on
│    ├── MockDataProvider   (Phase 1)          │  ← reads mock-data/*.json
│    └── ApiDataProvider    (Phase 2+)         │  ← calls real APIs (same interface)
└─────────────────────────────────────────────┘
```

### 3.2 The Data Service Interface (Contract)

Define an interface describing **what** data the app needs, not **how** it is fetched. Method names and return shapes must match the mock data shapes in `08-mock-data-specification.md` and the entities in `06-data-model-document.md`.

Illustrative interface (language-agnostic pseudocode):

```
interface AssetDataService {
  getStates(): State[]
  getDistricts(stateId): District[]
  getZones(districtId): Zone[]
  getPanchayats(zoneId): Panchayat[]
  getCategories(): AssetCategory[]

  getAssets(filter?: AssetFilter): Asset[]      // filter: { zoneId?, panchayatId?, categoryId?, assetType?, status?, query? }
  getAssetById(assetId): Asset | null
  getPhotosByAssetId(assetId): Photo[]

  getDashboardSummary(): DashboardSummary       // totals, zone-wise, panchayat-wise, lifecycle counts
}
```

- **Phase 1:** `MockDataProvider implements AssetDataService` by loading and filtering the JSON in memory.
- **Phase 2+:** `ApiDataProvider implements AssetDataService` by calling REST/GraphQL endpoints (see `06-data-model-document.md` §6) and returning the **same shapes**.
- **Wiring:** The application resolves which provider to use via a single configuration point (e.g., a factory or dependency injection). Swapping providers must require **no change** to any screen.

### 3.3 Shared Domain Services

- **Lifecycle service:** one function computes `current_age`, `remaining_life`, and `status` per `07-business-rules.md`. Every screen (dashboard, list, detail, lifecycle view) uses this — no inline duplication.
- **Aggregation service:** computes dashboard counts (total, by zone, by panchayat, by status) from the asset set.

> If you find yourself computing status or counts inside a component, stop and move it to the shared service.

---

## 4. Folder Structure

A suggested structure (adapt names to the chosen stack; keep the separation):

```
ramp-poc/
├── docs/                      # this documentation set
├── public/
│   └── mock-images/           # placeholder asset photos
├── mock-data/                 # JSON collections (states, districts, ... assets, photos)
│   ├── states.json
│   ├── districts.json
│   ├── zones.json
│   ├── panchayats.json
│   ├── categories.json
│   ├── assets.json
│   └── photos.json
├── src/
│   ├── data/                  # DATA LAYER (the contract + providers)
│   │   ├── AssetDataService.ts        # interface/contract
│   │   ├── MockDataProvider.ts        # Phase 1 implementation
│   │   ├── ApiDataProvider.ts         # Phase 2+ stub (same interface)
│   │   └── dataServiceFactory.ts      # resolves active provider via config
│   ├── domain/                # BUSINESS LOGIC (pure, testable)
│   │   ├── lifecycle.ts               # age/remaining/status computation
│   │   ├── aggregation.ts             # dashboard counts
│   │   └── types.ts                   # entity & filter types (match Doc 06)
│   ├── components/            # REUSABLE UI PRIMITIVES
│   │   ├── layout/                    # AppShell, Header, Breadcrumb
│   │   ├── Card/
│   │   ├── DataTable/
│   │   ├── StatusBadge/
│   │   ├── EmptyState/
│   │   ├── FilterBar/
│   │   └── PhotoThumb/
│   ├── screens/              # SCREEN-LEVEL COMPONENTS (one per Doc 04 screen)
│   │   ├── Dashboard/
│   │   ├── ZoneList/
│   │   ├── PanchayatList/
│   │   ├── CategoryList/
│   │   ├── AssetList/
│   │   ├── AssetDetail/
│   │   ├── PhotoGallery/
│   │   ├── LocationView/
│   │   └── LifecycleView/
│   ├── hooks/                # VIEW LOGIC (e.g., useAssets, useDashboard)
│   ├── routes/               # navigation/routing + breadcrumb config
│   ├── styles/               # tokens, theme (see Doc 11)
│   └── app entry             # composition root (wires factory → providers)
└── tests/                    # unit tests (lifecycle boundaries, aggregation, filters)
```

**Folder rules:**
- `screens/` may import from `components/`, `hooks/`, `domain/`, and `data/` (via the interface) — but must **not** read `mock-data/*.json` directly.
- `domain/` is pure logic: no UI imports, no data-fetching; easily unit-tested.
- `data/` is the only place that knows about mock JSON (Phase 1) or HTTP (Phase 2+).

---

## 5. Reusable Components

Build these once and reuse across screens (see `05-wireframe-document.md` and `11-ui-ux-guidelines.md`):

| Component | Responsibility | Used By |
|---|---|---|
| `AppShell` | Header + breadcrumb + content outlet | All screens |
| `Breadcrumb` | Render and navigate the hierarchy path | All drill-down screens |
| `Card` | Generic metric/category/summary card | Dashboard, Category List |
| `DataTable` | Sortable/columnar list; collapses to cards on narrow widths | Asset List |
| `StatusBadge` | Render computed status with consistent color | Lists, detail, lifecycle, dashboard |
| `EmptyState` | Standard empty/no-results presentation | Lists, gallery, location |
| `FilterBar` | Search + filters with AND logic + chips + reset | Asset List |
| `PhotoThumb` / `PhotoOverlay` | Thumbnail + enlarged view + placeholder | Photo Gallery, Asset Detail |
| `MapPin` / `CoordinateView` | Simple pin/coordinate display + unavailable state | Location View, Asset Detail |
| `LifecyclePanel` | Figures + status (+ optional life-consumed bar) | Asset Detail, Lifecycle View |

**Component rules:**
- Components receive data via props; they do not fetch or compute business logic themselves.
- `StatusBadge` takes a status value produced by the shared lifecycle service — it never computes status.
- Keep components presentation-focused and stateless where possible; lift state to hooks/screens.

---

## 6. Separation of Concerns (Enforced)

| Concern | Lives In | Must NOT |
|---|---|---|
| Data fetching | `data/` providers | …happen in screens/components |
| Business logic (status, counts) | `domain/` services | …be duplicated inline in UI |
| Orchestration / view state | `hooks/` | …contain raw fetch or status math |
| Presentation | `components/`, `screens/` | …read JSON or compute domain logic |
| Navigation/context | `routes/` + passed state | …be hard-coded per screen |

> A quick self-check: if swapping `MockDataProvider` for `ApiDataProvider` would force edits to any screen or component, the separation has been violated — fix the abstraction.

---

## 7. Coding Standards

### 7.1 General
- Prefer clarity over cleverness; the POC is read by future sessions.
- Single responsibility per module/function.
- Name things by domain (`Asset`, `Panchayat`, `lifecycleStatus`) consistent with Doc 06/07.
- No magic numbers for thresholds — define them once (e.g., `NEAR_EXPIRY_YEARS = 5`).

### 7.2 Data & Types
- Mirror entity field names from `06-data-model-document.md` (`snake_case` in JSON; map to the app's casing at the data layer if desired).
- Define explicit types/interfaces for every entity and for `AssetFilter` and `DashboardSummary`.
- Treat mock JSON shapes as the API contract; do not diverge.

### 7.3 Business Logic
- Implement lifecycle computation exactly per `07-business-rules.md`, including boundaries (RL == 5 → Near Expiry; RL == 0 → Expired) and Unknown handling.
- Centralize thresholds and hierarchy order so future changes are one-line edits.
- Unit-test boundaries and Unknown cases.

### 7.4 UI
- Follow `11-ui-ux-guidelines.md` for layout, cards, tables, status colors, and responsiveness.
- Always implement empty/error/edge states (no photos, no coordinates, no results, unknown status).
- Keep navigation consistent: breadcrumbs everywhere below the dashboard; sub-views return to detail.

### 7.5 Error Handling
- The data layer returns predictable empties (`[]`, `null`) rather than throwing for "not found".
- Components render empty states for empties; never crash on missing optional fields.
- Validate coordinates and lifecycle inputs at the boundary; degrade gracefully.

### 7.6 Testing
- Unit tests for `domain/lifecycle` (boundary table from Doc 07) and `domain/aggregation` (counts from Doc 08 §9).
- Tests for `MockDataProvider` filtering (zone/panchayat/category/status/query, AND logic).
- Keep tests provider-agnostic where possible so they also validate a future `ApiDataProvider`.

---

## 8. Step-by-Step Build Order (For a Fresh Session)

1. **Read** Docs 03, 06, 07, 08, 11 (requirements, model, rules, mock data, UI).
2. **Scaffold** the folder structure (§4).
3. **Author** `mock-data/*.json` per Doc 08 (with status variety).
4. **Define** entity/types (`domain/types.ts`) matching Doc 06.
5. **Implement** `domain/lifecycle.ts` and unit-test boundaries (Doc 07).
6. **Define** `AssetDataService` interface and implement `MockDataProvider`.
7. **Implement** `domain/aggregation.ts` for dashboard counts.
8. **Build** reusable components (§5).
9. **Build** screens in roadmap order: hierarchy + list → detail + sub-views → dashboard + search/filter (Doc 09).
10. **Add** empty/error states and responsive behavior (Doc 11).
11. **QA** against Acceptance Criteria (Doc 03) and Success Criteria (Doc 01).

---

## 9. Future Migration Playbook (Phase 2+)

When asked to connect real data:
1. **Do not touch screens.** Implement `ApiDataProvider` against the **same** `AssetDataService` interface.
2. Map API responses to the documented shapes (Doc 06/08). Keep field names stable.
3. Move lifecycle computation server-side if desired, but keep the **client contract** identical (the UI still receives the same status values).
4. Switch the provider in `dataServiceFactory` via configuration.
5. Verify zero UI changes were needed — that verification is the proof the POC was built correctly.
6. Only then add write operations and deferred modules (Doc 09 Phases 3–5).

---

## 10. Do / Don't Quick Reference

**Do**
- Route all data access through `AssetDataService`.
- Compute lifecycle status in one shared place.
- Keep `domain/` pure and tested.
- Build reusable, presentation-only components.
- Handle every empty/edge state.

**Don't**
- Read `mock-data/*.json` from a screen/component.
- Store or hard-code lifecycle status.
- Add a database, ORM, or persistence.
- Build create/edit/delete UI in Phase 1.
- Build excluded modules.
- Over-engineer toward production ERP — but don't block it either.
