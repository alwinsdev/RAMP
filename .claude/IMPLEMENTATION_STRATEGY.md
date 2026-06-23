# IMPLEMENTATION_STRATEGY.md — RAMP

> The build plan for Claude Code: the order to build things, the sprint structure, what to prioritize, what comes later, and how to avoid technical debt. Derived from `docs/09-development-roadmap.md` and `docs/10-claude-development-guide.md`.

**Guiding idea:** Build the **future-ready skeleton first**, then features in dependency order, so the POC is demonstrable *and* migrates to APIs/database later with no UI rewrite.

---

## 1. Development Sequence (Build Order)

Follow this order for a fresh build. It front-loads the architecture and the shared logic that everything else depends on.

```
1. Read the docs        → docs/03 (FRS), 06 (model), 07 (rules), 08 (mock data), 11 (UI)
2. Scaffold structure   → the folder layout in DEVELOPMENT_RULES.md §2
3. Author mock data     → mock-data/*.json per docs/08 (with status variety incl. Unknown)
4. Define types         → domain/types.ts matching docs/06 (+ AssetFilter, DashboardSummary)
5. Define constants     → domain/constants.ts (NEAR_EXPIRY_YEARS=5, hierarchy order, status set, filter keys)
6. Lifecycle service    → domain/lifecycle.ts; UNIT-TEST boundaries (RL=14/5/-1/0, Unknown)
7. Data contract        → data/AssetDataService.ts (the interface)
8. Mock provider        → data/MockDataProvider.ts implements the interface over JSON
9. Provider factory     → data/dataServiceFactory.ts (config-driven selection)
10. Aggregation service → domain/aggregation.ts (totals, by zone/panchayat/category/status)
11. UI primitives       → AppShell, Breadcrumb, Card, DataTable, StatusBadge, EmptyState, FilterBar, PhotoThumb
12. Screens (in order)  → hierarchy + list  →  detail + sub-views  →  dashboard + search/filter
13. Edge & responsive   → empty/error states everywhere; table→cards; breakpoints (docs/11)
14. QA                  → against acceptance criteria (docs/03) and success criteria (docs/01)
```

**Why this order:** types + constants + lifecycle + the data contract are the foundation every screen consumes. Building the **data seam and shared domain logic before any screen** is what guarantees future-readiness (see `ARCHITECTURE_RULES.md`). Authoring mock data early gives every later step realistic inputs.

---

## 2. Recommended Sprint Order

Sprints are 1-week increments for a small team (adjust cadence; the **sequence and dependencies** are what matter). Each sprint has a clear exit criterion.

### Sprint 0 — Foundation & Architecture
**Objective:** Establish the future-ready skeleton before feature work.
- Scaffold the folder structure (`DEVELOPMENT_RULES.md` §2).
- Define the **data/service interface** (the contract the UI depends on).
- Implement the **mock data provider** behind that interface.
- Author seed mock data (`docs/08`) including variety for **all** statuses.
- Implement the **shared lifecycle/status** function (`BUSINESS_RULES.md`).
- Build reusable UI primitives (shell, breadcrumb, card, table, status badge, empty state).

**Exit criteria:** Mock provider returns all entities; lifecycle passes unit tests for boundary cases; app shell renders.

### Sprint 1 — Hierarchy Navigation & Asset List
**Objective:** Make the hierarchy navigable down to a list of assets.
- Zone List, Panchayat List, Asset Category List screens.
- Breadcrumb wiring and upward navigation.
- Asset List (table/cards) with status badges.
- Drill-down context passing (zone → panchayat → category → list).

**Exit criteria:** A user can drill from a district down to an asset list; breadcrumbs work; badges show computed status. *(Maps to FR-NAV-\*, FR-CAT-\*, FR-ASST-01/03/05.)*

### Sprint 2 — Asset Detail, Photos, Location, Lifecycle
**Objective:** Complete the leaf experience.
- Asset Detail with all five information groups.
- Photo Gallery (thumbnails, enlarge, empty state, broken-image placeholder).
- Map/Location View (pin, coordinates, "location unavailable").
- Lifecycle View (figures + status + optional life-consumed visual).

**Exit criteria:** From any asset, the user can view full detail and open photos, location, and lifecycle; edge cases (no photos, no coordinates, unknown lifecycle) handled. *(Maps to FR-ASST-02/04, FR-PHOTO-\*, FR-LOC-\*, FR-LIFE-\*.)*

### Sprint 3 — Dashboard & Search/Filter
**Objective:** Deliver insight and fast retrieval.
- Dashboard: total assets, category count, zone-wise, panchayat-wise, lifecycle summary, health indicators.
- Dashboard drill-down shortcuts (zone/panchayat/category/status → filtered Asset List).
- Search (name/number) + filters (zone, panchayat constrained by zone, type, status) with AND logic, chips, result count, reset.

**Exit criteria:** Dashboard metrics reconcile with mock data; clicking a metric opens the correct filtered list; search and filters behave per `BUSINESS_RULES.md`. *(Maps to FR-DASH-\*, FR-SRCH-\*.)*

### Sprint 4 — Polish, Responsiveness, QA & Demo
**Objective:** Make it demo-ready and validate against acceptance criteria.
- Responsive behavior (desktop/tablet) per `UI_RULES.md` (cards stack, table → cards).
- Empty/error states across all screens.
- Visual consistency pass (status colors, spacing, typography).
- QA against all acceptance criteria (`docs/03`).
- Prepare a demo script following the canonical user journey.

**Exit criteria:** All success criteria in `docs/01` demonstrably met; stakeholder demo delivered.

### Sprint Summary

| Sprint | Theme | Primary Modules | Key Exit Criterion |
|---|---|---|---|
| 0 | Foundation & Architecture | Data layer, lifecycle engine, UI primitives | Future-ready skeleton + tested lifecycle logic |
| 1 | Hierarchy & List | Navigation, Category, Asset List | Drill-down to asset list works |
| 2 | Detail & Sub-views | Asset, Photos, Location, Lifecycle | Full asset detail + sub-views with edge cases |
| 3 | Insight & Retrieval | Dashboard, Search & Filter | Dashboard reconciles; search/filter correct |
| 4 | Polish & QA | All | All acceptance criteria met; demo ready |

---

## 3. Priority Modules (MoSCoW for the POC)

### Must Have (POC fails without these)
1. **Data/service layer abstraction + mock provider** (enables future-readiness).
2. **Shared lifecycle/status computation.**
3. **Hierarchy navigation end-to-end** with breadcrumbs.
4. **Asset list and asset detail** (all five info groups).
5. **Lifecycle monitoring** with correct status and boundaries.
6. **Dashboard core metrics** with drill-down.
7. **Search and filter** with AND logic.

### Should Have (strongly desired)
8. **Photo gallery** with enlarge + empty/placeholder states.
9. **Map/location view** with "location unavailable" handling.
10. **Responsive layout** for tablet widths.
11. **Health distribution visual** on the dashboard.

### Could Have (if time allows)
12. **Sorting** on the asset list.
13. **Life-consumed progress visual** on the lifecycle view.
14. **Per-zone/per-panchayat mini health indicators.**

### Won't Have (explicitly out for the POC)
- Any **database or persistence**.
- **UI create/edit/delete** operations.
- **Maintenance, inspections, work orders, notifications, approvals, native mobile.**
- Production **authentication/authorization, RBAC, audit, export** engines.

**Build-priority implication:** Must-Have items map to Sprints 0–3 and are non-negotiable for a successful POC. Should/Could items are pulled in as time allows in Sprints 2–4. Never start a Won't-Have item.

---

## 4. Future Enhancements (Phases 2–5)

The POC is structured so these are **incremental additions**, not rewrites. Do **not** build them now; build *toward* them.

| Phase | Name | What's added | How it stays low-risk |
|---|---|---|---|
| Phase 2 | **API Enablement** | Replace mock provider with live **read** APIs | Implement `ApiDataProvider` against the **same** `AssetDataService` interface; switch via factory config; **zero UI change** |
| Phase 3 | **Database & Persistence** | Introduce DB; enable **write** ops (create/edit/delete) | Each entity → one table/resource (1:1 with mock collections); write methods added behind the same interface; reads unchanged |
| Phase 4 | **Functional Expansion** | Maintenance, inspections, work orders, notifications, approvals | New modules layered on the established data layer and navigation patterns |
| Phase 5 | **Hardening & Mobile** | AuthN/AuthZ + RBAC, reporting/export, audit, native mobile field app | Built on a stable, well-separated core |

**The migration acceptance test (Phase 2):** implement `ApiDataProvider`, switch the factory, and **verify zero UI changes were required.** That verification is the proof the POC was built correctly. Migration mechanics: `ARCHITECTURE_RULES.md` §7–8.

---

## 5. Technical Debt Avoidance Guidelines

These are the practices that keep the POC clean and the future migration painless. Treat violations as defects.

| ID | Guideline | Why it matters |
|---|---|---|
| TD-01 | **Never read `mock-data/*.json` outside `data/`.** | The data source must be swappable; JSON references leaking into UI block the API migration. |
| TD-02 | **One lifecycle implementation, one aggregation implementation** in `domain/`. | Duplicated domain logic drifts and breaks consistency (BR-PR-02). |
| TD-03 | **Never store computed lifecycle status.** | Stored status goes stale as time passes; status must be recomputed (BR-LC-04). |
| TD-04 | **No magic numbers** — centralize thresholds, hierarchy order, status set, filter keys in `domain/constants`. | Future rule changes become one-line edits (BR-PR-03). |
| TD-05 | **No hard-coded counts.** All numbers come from the aggregation service over the live dataset. | Hard-coded totals diverge from data and break in every later phase (BR-DI-05). |
| TD-06 | **Keep data-layer method signatures async-ready.** | So introducing the API provider doesn't force call-site rewrites (`ARCHITECTURE_RULES.md` SL-09). |
| TD-07 | **Keep data shapes stable and matching `docs/06`/`docs/08`.** | Shapes are the API contract; drift forces UI changes later (SL-06). |
| TD-08 | **Components are presentation-only** — no fetching, no domain math, no `data/` imports. | Preserves separation; keeps the migration UI-invisible (CO-01, FS-04). |
| TD-09 | **Implement every empty/loading/error state now.** | Future API latency/failures reuse these states with no redesign (EH-10, `UI_RULES.md` §9). |
| TD-10 | **Don't over-engineer toward production ERP** (no RBAC/audit/write workflows in the POC) — **but never block them.** | Keeps the POC lean while preserving the upgrade path (POC constraint). |
| TD-11 | **Build reusable primitives once; reuse everywhere.** | Per-screen forks of cards/tables/badges create maintenance debt (RU-01). |
| TD-12 | **Unit-test the rules (lifecycle boundaries, aggregation reconciliation, filter logic).** | Tests protect correctness through refactors and the provider swap (TS-01..05). |
| TD-13 | **Provider selection lives in one factory/composition root.** | Scattered selection logic is the classic blocker to swapping data sources (ARCH §1.6). |
| TD-14 | **Don't use browser storage as a datastore.** | The only Phase-1 source is the mock provider (ST-06). |

**The single self-check that prevents most debt:**

> *If I swapped `MockDataProvider` for `ApiDataProvider`, would any screen, component, hook, or domain service need to change?*
> If **yes**, stop and fix the abstraction before continuing.

---

## 6. Definition of Done (POC)

The POC is complete when:

- ✅ All **Must-Have** modules are implemented and pass their acceptance criteria (`docs/03`).
- ✅ The canonical **user journey** works end-to-end (Dashboard → … → Asset Detail → sub-views).
- ✅ **Lifecycle status** is computed correctly everywhere, including boundaries and Unknown.
- ✅ **Dashboard metrics reconcile** with the mock dataset and every metric drills down correctly.
- ✅ **Search and filter** behave per `BUSINESS_RULES.md` (AND/OR semantics, chips, reset, result count).
- ✅ Every screen handles **loading, empty, and error** states; no dead-ends.
- ✅ The app is **responsive** to tablet widths (table → cards, grids reflow).
- ✅ **Status colors, breadcrumbs, spacing, and terminology** are consistent app-wide.
- ✅ **No JSON reads in UI, no inline domain logic, no hard-coded counts, no stored status.**
- ✅ A hypothetical **`ApiDataProvider` swap** would require **zero UI changes** — verified by inspection.
- ✅ **Success criteria** in `docs/01` are demonstrably met and a stakeholder demo is delivered.

---

*End of IMPLEMENTATION_STRATEGY.md*
