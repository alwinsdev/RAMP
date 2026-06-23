# Project Vision — Rural Asset Management Platform (RAMP)

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-01 |
| Document Title | Project Vision |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Sponsors, Product Owners, Architects, Development Team |
| Related Documents | 02-BRD, 03-FRS, 09-Development Roadmap |

---

## 1. Purpose of This Document

This document defines the vision, mission, and guiding objectives for the Rural Asset Management Platform (RAMP) Proof of Concept (POC). It establishes the "why" behind the initiative and the boundaries within which the POC will be designed and built. It is the anchor reference that all downstream documents (requirements, screens, data model, roadmap) trace back to.

This is a **Proof of Concept**, not a production Enterprise Resource Planning (ERP) system. The POC validates the concept, the user journeys, and the information architecture using **hard-coded mock JSON data**. No database is implemented in Phase 1. However, every architectural and design decision is made to be **future-ready**, so the mock data layer can later be swapped for live APIs and a database without rewriting screens or business logic.

---

## 2. Vision

> To establish a single, trusted, and intuitive digital window into every non-movable public asset under the Panchayat and Rural Development administration — enabling officials at every level of the State → District → Zone → Panchayat hierarchy to see what they own, where it is, what condition it is in, and when it needs attention.

RAMP aims to replace fragmented registers, spreadsheets, and tribal knowledge with a structured, navigable, and visually clear asset repository that any administrator can understand within minutes.

---

## 3. Mission

To deliver, through an incremental and validated approach, a centralized rural asset management capability that:

- Consolidates asset records into one authoritative repository.
- Reflects the real administrative hierarchy that rural governance operates under.
- Makes the physical location and lifecycle health of every asset immediately visible.
- Surfaces actionable insight (counts, distribution, near-expiry warnings) through a clear dashboard.
- Is engineered from day one to grow from a mock-data POC into a fully API- and database-backed platform.

---

## 4. Business Objectives

The platform exists to serve the following business objectives:

1. **Centralized Asset Repository** — Provide one place where all rural public assets are recorded and retrievable.
2. **Asset Categorization** — Organize assets into meaningful categories (Educational, Healthcare, Water Infrastructure, Public Infrastructure) and sub-types.
3. **Geographic Location Tracking** — Capture and display latitude/longitude and address for each asset.
4. **Asset Lifecycle Monitoring** — Track construction year and expected life, and automatically derive current age, remaining life, and health status.
5. **Dashboard Analytics** — Offer summarized, drill-downable views of asset counts and health across the hierarchy.
6. **Administrative Hierarchy Visibility** — Allow navigation that mirrors State → District → Zone → Panchayat → Category → Asset.
7. **Asset Photo Management** — Associate and display photographic evidence for each asset.
8. **Search and Filter Capabilities** — Let users quickly locate assets by attributes and narrow large lists.

---

## 5. POC Objectives

The POC has a deliberately narrow, validation-focused set of objectives:

1. **Prove the information architecture** — Demonstrate that the State → District → Zone → Panchayat → Category → Asset model is navigable and understandable.
2. **Prove the core user journey** — Validate the path from Dashboard → drill-down → Asset Detail → Photos/Location/Lifecycle.
3. **Prove lifecycle logic** — Demonstrate automatic computation of asset age, remaining life, and health status (Healthy / Near Expiry / Expired).
4. **Prove dashboard value** — Show that aggregated counts and health indicators provide meaningful, at-a-glance insight.
5. **Prove future-readiness** — Demonstrate that the application is structured so the mock data layer can be replaced with APIs/database without UI or logic rewrites.
6. **Generate stakeholder buy-in** — Provide a tangible artifact for sponsor demonstrations and funding/scope decisions.

The POC is explicitly **not** trying to be feature-complete, multi-tenant, secured to production standards, or performance-tuned for national scale.

---

## 6. Success Criteria

The POC is considered successful if all of the following are demonstrably true:

| # | Success Criterion | How It Is Measured |
|---|---|---|
| SC-01 | The full hierarchy is navigable end-to-end | A user can drill from Dashboard to an individual Asset Detail using only on-screen navigation |
| SC-02 | Lifecycle status is computed correctly | For any asset, the displayed status matches the rules in `07-business-rules.md` |
| SC-03 | Dashboard reflects underlying mock data | All counts (total assets, categories, zone-wise, panchayat-wise) reconcile with the mock dataset |
| SC-04 | Search and filter return correct results | Filtering by zone, panchayat, category, or status returns only matching assets |
| SC-05 | Asset detail is complete | Detail view shows administrative, location, lifecycle, and media information together |
| SC-06 | Photos and location render | Each asset can display its associated photos and a map/coordinate view |
| SC-07 | Architecture is data-source agnostic | Swapping the mock data provider for a stub API requires no change to screen components |
| SC-08 | Stakeholders approve direction | Sponsor sign-off to proceed to the next phase |

---

## 7. Scope

### 7.1 In Scope (POC)

- Dashboard with summary metrics and drill-down navigation
- Asset Category Management (view/browse categories)
- Asset Management (list, detail)
- Asset Location Management (address, latitude/longitude, map/coordinate display)
- Asset Photo Management (gallery, associated images)
- Asset Lifecycle Monitoring (age, remaining life, health status)
- Search & Filter across assets
- Hierarchy Navigation (State → District → Zone → Panchayat → Category → Asset)
- Hard-coded mock JSON data as the data source
- API-shaped, future-ready architecture

### 7.2 Out of Scope (POC)

- Maintenance Management
- Inspection Management
- Work Orders
- Notifications / Alerts delivery
- Approval Workflows
- Mobile (native) Application
- Live database implementation
- Authentication/authorization to production standards
- Reporting/export engines, audit trails, and role-based access control (RBAC)

> Out-of-scope items are not rejected — they are deferred. The architecture leaves room for them (see `02-business-requirements-document.md` → Future Scope).

---

## 8. Assumptions

| # | Assumption |
|---|---|
| A-01 | The POC will run entirely on mock JSON data; no live integrations exist in Phase 1. |
| A-02 | The administrative hierarchy (State → District → Zone → Panchayat) is stable and accurately represents the target domain. |
| A-03 | Asset categories and sub-types provided in the brief are representative and sufficient for the POC. |
| A-04 | Lifecycle status thresholds (Healthy > 5 years; Near Expiry ≤ 5 years; Expired ≤ 0 years) are correct and agreed. |
| A-05 | "Current Year" used in lifecycle math is the system/application year at runtime. |
| A-06 | A modern web browser is the only client target for the POC. |
| A-07 | Mock data volume is small enough to load entirely in-memory without pagination infrastructure. |
| A-08 | Stakeholders are available for demos and feedback during the POC build. |

---

## 9. Constraints

| # | Constraint |
|---|---|
| C-01 | **No database** may be implemented in Phase 1; all data must be mock JSON. |
| C-02 | The system must **not** be over-engineered as a production ERP. |
| C-03 | Despite mock data, the codebase must remain **future-ready** for API/database migration with minimal change. |
| C-04 | Excluded modules (maintenance, inspections, work orders, notifications, approvals, mobile) must not be built in the POC. |
| C-05 | All data access must be abstracted behind a service/data layer so the UI never reads mock files directly. |
| C-06 | Effort and timeline are POC-scale; deliverables prioritize validation over completeness. |

---

## 10. Guiding Principles

These principles govern every downstream decision and are repeated across the documentation set:

1. **Mock now, API later** — Treat all data as if it already comes from an API; only the implementation behind the data layer is mocked.
2. **Separation of concerns** — UI, business logic, and data access are cleanly separated so each can evolve independently.
3. **Hierarchy first** — The administrative hierarchy is the backbone of navigation, data, and dashboards.
4. **Derive, don't store, lifecycle status** — Health status is always computed from construction year and expected life, never hard-stored, so it stays correct over time.
5. **Future migration readiness** — Every entity, screen, and flow is documented with its eventual database/API form in mind.

---

## 11. Future Database Migration Note

Although Phase 1 uses mock JSON, this vision anticipates a future where:

- Each mock JSON collection (states, districts, zones, panchayats, categories, assets, photos) maps to a database table or API resource.
- The in-memory data service is replaced by an HTTP/API client implementing the same interface.
- Lifecycle computations remain in the application/service layer (or move to the API) but the **contract** the UI depends on does not change.

This forward-compatibility is a first-class objective, not an afterthought. See `06-data-model-document.md` and `10-claude-development-guide.md` for the concrete patterns that make this possible.
