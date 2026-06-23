# Development Roadmap — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-09 |
| Document Title | Development Roadmap |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Product Owner, Delivery Lead, Developers |
| Related Documents | 02-BRD, 03-FRS, 10-Claude Dev Guide |

---

## 1. Introduction

This roadmap sequences the delivery of the RAMP POC and outlines the path beyond it. It defines phases, a sprint breakdown for the POC, development priorities, risks, and dependencies. The POC is delivered against mock JSON with a future-ready architecture; later phases introduce APIs, a database, write operations, and the deferred modules.

**Timeboxing assumption:** Sprints are described as 1-week increments for a small team. Adjust to your cadence; the *sequence and dependencies* are what matter.

---

## 2. Phase Breakdown

| Phase | Name | Goal | Data Source | Key Outcome |
|---|---|---|---|---|
| **Phase 1** | POC (this effort) | Validate concept, journeys, lifecycle logic, and dashboard value | **Mock JSON** | Demonstrable, future-ready POC |
| Phase 2 | API Enablement | Replace mock provider with live APIs (read) | API (read) over a real backend | Same UI, real data source |
| Phase 3 | Database & Persistence | Introduce the database; enable write operations (create/edit/delete) | Database via APIs | Editable, persistent records |
| Phase 4 | Functional Expansion | Add deferred modules (maintenance, inspections, work orders, notifications, approvals) | Database/APIs | Operational asset management |
| Phase 5 | Platform Hardening & Mobile | AuthN/AuthZ + RBAC, reporting/export, audit, native mobile field app | Production stack | Production-grade platform |

> **Hard boundary for Phase 1:** No database, no UI write operations, no excluded modules. Everything is structured so Phases 2–5 are incremental, low-risk additions.

---

## 3. POC (Phase 1) Sprint Breakdown

### Sprint 0 — Foundation & Architecture (Setup)
**Objective:** Establish the future-ready skeleton before feature work.
- Project scaffolding (folder structure per `10-claude-development-guide.md`).
- Define the **data/service layer interface** (the contract the UI depends on).
- Implement the **mock data provider** behind that interface (loads `mock-data/*.json`).
- Author seed mock data (`08-mock-data-specification.md`), including variety for all statuses.
- Implement the **shared lifecycle/status computation** function/service (`07-business-rules.md`).
- Set up reusable UI primitives (layout shell, breadcrumb, card, table, status badge, empty state).

**Exit criteria:** Mock provider returns all entities; lifecycle function passes unit tests for boundary cases; app shell renders.

---

### Sprint 1 — Hierarchy Navigation & Asset List
**Objective:** Make the hierarchy navigable down to a list of assets.
- Zone List, Panchayat List, Asset Category List screens.
- Breadcrumb wiring and upward navigation.
- Asset List screen (table/cards) with status badges.
- Drill-down context passing (zone → panchayat → category → list).

**Exit criteria:** A user can drill from a state down to an asset list; breadcrumbs work; status badges show computed status. (Maps to FR-NAV-*, FR-CAT-*, FR-ASST-01/03/05.)

---

### Sprint 2 — Asset Detail, Photos, Location, Lifecycle
**Objective:** Complete the leaf experience.
- Asset Detail screen with all five information groups.
- Photo Gallery (thumbnails, enlarge, empty state, placeholder for broken images).
- Map/Location View (pin, coordinates, "location unavailable" state).
- Lifecycle View (figures + status + optional life-consumed visual).

**Exit criteria:** From any asset, the user can view full detail and open photos, location, and lifecycle sub-views; edge cases (no photos, no coordinates, unknown lifecycle) handled. (Maps to FR-ASST-02/04, FR-PHOTO-*, FR-LOC-*, FR-LIFE-*.)

---

### Sprint 3 — Dashboard & Search/Filter
**Objective:** Deliver insight and fast retrieval.
- Dashboard: total assets, category count, zone-wise, panchayat-wise, lifecycle summary, health indicators.
- Dashboard drill-down shortcuts (zone/panchayat/category/status → filtered Asset List).
- Search (name/number) and filters (zone, panchayat constrained by zone, type, status) with AND logic, active chips, result count, reset.

**Exit criteria:** Dashboard metrics reconcile with mock data; clicking a metric opens the correct filtered list; search and filters behave per `07-business-rules.md`. (Maps to FR-DASH-*, FR-SRCH-*.)

---

### Sprint 4 — Polish, Responsiveness, QA & Demo
**Objective:** Make it demo-ready and validate against acceptance criteria.
- Responsive behavior (desktop/tablet) per `11-ui-ux-guidelines.md` (cards stack, table → cards).
- Empty/error states across all screens.
- Visual consistency pass (status colors, spacing, typography).
- QA pass against all Acceptance Criteria in `03-functional-requirements-specification.md`.
- Prepare demo script following the canonical user journey.

**Exit criteria:** All success criteria in `01-project-vision.md` demonstrably met; stakeholder demo delivered.

---

### Sprint Summary Table

| Sprint | Theme | Primary Modules | Key Exit Criterion |
|---|---|---|---|
| 0 | Foundation & Architecture | Data layer, lifecycle engine, UI primitives | Future-ready skeleton + tested lifecycle logic |
| 1 | Hierarchy & List | Navigation, Category, Asset List | Drill-down to asset list works |
| 2 | Detail & Sub-views | Asset, Photos, Location, Lifecycle | Full asset detail + sub-views with edge cases |
| 3 | Insight & Retrieval | Dashboard, Search & Filter | Dashboard reconciles; search/filter correct |
| 4 | Polish & QA | All | All acceptance criteria met; demo ready |

---

## 4. Development Priorities

Prioritized using a MoSCoW lens for the POC:

### Must Have (POC fails without these)
1. Data/service layer abstraction + mock provider (enables future-readiness).
2. Shared lifecycle/status computation.
3. Hierarchy navigation end-to-end with breadcrumbs.
4. Asset list and asset detail (all five info groups).
5. Lifecycle monitoring with correct status and boundaries.
6. Dashboard core metrics with drill-down.
7. Search and filter with AND logic.

### Should Have (strongly desired)
8. Photo gallery with enlarge + empty/placeholder states.
9. Map/location view with "location unavailable" handling.
10. Responsive layout for tablet widths.
11. Health distribution visual on the dashboard.

### Could Have (nice if time allows)
12. Sorting on the asset list.
13. Life-consumed progress visual on the lifecycle view.
14. Per-zone/per-panchayat mini health indicators.

### Won't Have (explicitly out for POC)
- Any database or persistence.
- UI create/edit/delete operations.
- Maintenance, inspections, work orders, notifications, approvals, native mobile.
- Production authentication/authorization, RBAC, audit, export engines.

---

## 5. Risks

| ID | Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|---|
| R-01 | Mock data and UI become tightly coupled, harming future-readiness | Medium | High | Enforce data/service layer; UI never reads JSON directly (architectural review in Sprint 0). |
| R-02 | Lifecycle logic duplicated/inconsistent across screens | Medium | High | Single shared computation function with unit tests; reuse everywhere. |
| R-03 | Scope creep toward production ERP features | Medium | High | Hold the "Won't Have" line; Product Owner gates scope. |
| R-04 | Mock dataset too thin to demonstrate value | Medium | Medium | Author a richer dataset with variety across zones/panchayats/statuses. |
| R-05 | Inconsistent status thresholds/boundary handling | Low | High | Centralize thresholds; test exact-5 and exact-0 boundaries. |
| R-06 | Denormalized labels in mock data drift from FK ids | Medium | Medium | Validate references in dev; treat ids as canonical. |
| R-07 | Map rendering complexity inflates effort | Medium | Medium | Use a simple coordinate/pin display for the POC; defer rich mapping. |
| R-08 | Future API shape diverges from mock shape | Medium | High | Treat mock JSON shapes as the API contract; document in Doc 06/08. |
| R-09 | Responsive/edge-case polish deprioritized until too late | Medium | Medium | Bake empty/error states into each sprint, not just Sprint 4. |
| R-10 | Stakeholder availability for feedback slips | Low | Medium | Schedule demos at sprint boundaries up front. |

---

## 6. Dependencies

### 6.1 Internal (Documentation) Dependencies
- `06-data-model-document.md` and `08-mock-data-specification.md` must be settled before Sprint 0 data work.
- `07-business-rules.md` (lifecycle thresholds, search/filter semantics) gates Sprint 0 lifecycle engine and Sprint 3 search/filter.
- `04-screen-flow-document.md` and `05-wireframe-document.md` gate UI work in Sprints 1–3.
- `10-claude-development-guide.md` (folder structure, separation of concerns) gates Sprint 0 scaffolding.
- `11-ui-ux-guidelines.md` gates the Sprint 4 polish/responsive pass.

### 6.2 Sequencing Dependencies
```
Sprint 0 (foundation) ──► Sprint 1 (hierarchy+list) ──► Sprint 2 (detail+subviews)
                                   │                              │
                                   └──────────────► Sprint 3 (dashboard+search) ──► Sprint 4 (polish+QA)
```
- Sprint 3 depends on the lifecycle engine (Sprint 0) and the asset list (Sprint 1).
- Sprint 2 depends on the asset list/navigation (Sprint 1).

### 6.3 External / Future-Phase Dependencies
- Phase 2 (API) depends on a backend exposing resources matching the mock shapes.
- Phase 3 (DB) depends on the logical model in `06-data-model-document.md` being implemented.
- Phase 4 modules depend on persistence (Phase 3) and on the asset repository proven in Phase 1.

---

## 7. Definition of Done (POC)

A POC increment is "done" when:
- It meets the relevant Acceptance Criteria in `03-functional-requirements-specification.md`.
- It uses the data/service layer (no direct mock-file access from UI).
- Lifecycle status comes from the shared computation.
- Empty/error/edge states are handled.
- It renders acceptably on desktop and tablet widths.
- It has been reviewed against `07-business-rules.md`.

---

## 8. Post-POC Transition Checklist (Looking Ahead)

When the POC is approved and Phase 2 begins:
1. Stand up read APIs returning the documented shapes (Doc 06/08).
2. Implement an API-backed provider behind the **same** data/service interface.
3. Swap the provider via configuration — verify zero UI changes required.
4. Introduce the database and persistence (Phase 3); add write flows.
5. Layer on deferred modules (Phase 4) and platform hardening (Phase 5).
