# Business Requirements Document (BRD) — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-02 |
| Document Title | Business Requirements Document |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Sponsors, Business Stakeholders, Product Owners, Architects |
| Related Documents | 01-Project Vision, 03-FRS, 06-Data Model, 09-Roadmap |

---

## 1. Executive Summary

The Rural Asset Management Platform (RAMP) is a centralized digital system for managing and monitoring **non-movable public assets** owned and operated under Panchayat and Rural Development Departments. Today, information about these assets — schools, health centres, water tanks, panchayat offices, community halls — is typically scattered across registers, local spreadsheets, and individual knowledge. This fragmentation makes it difficult to answer basic governance questions: *How many assets do we have? Where are they? What condition are they in? Which ones are nearing the end of their useful life?*

RAMP addresses this by providing a single, hierarchy-aware repository with a dashboard, drill-down navigation, location tracking, photo evidence, and automated lifecycle health monitoring.

This document describes the business requirements for a **Proof of Concept (POC)**. The POC deliberately uses **hard-coded mock JSON data** and implements **no database** in Phase 1. Its purpose is to validate the concept, the user journeys, and the information model before any investment in production infrastructure. Critically, the POC is architected to be **future-ready**: the mock data layer is designed to be replaced by live APIs and a database with minimal change to screens or business logic.

---

## 2. Business Context

### 2.1 Current Situation

- Public rural assets are recorded inconsistently across multiple offices and formats.
- There is no single view of assets across the administrative hierarchy.
- Asset condition and remaining useful life are not systematically tracked, making planning reactive rather than proactive.
- Geographic and photographic records, where they exist, are not linked to a central asset record.

### 2.2 Problem Statement

Administrators lack a **centralized, structured, and visual** way to view, locate, and assess the health of the non-movable public assets under their jurisdiction, leading to poor visibility, difficult planning, and reactive maintenance decisions.

### 2.3 Opportunity

A centralized platform that mirrors the existing administrative hierarchy and presents asset data clearly would:

- Improve transparency and accountability across levels of administration.
- Enable proactive lifecycle planning by flagging near-expiry and expired assets.
- Provide a foundation for future capabilities (maintenance, inspections, work orders) once the core repository is proven.

### 2.4 Why a POC First

Building a full production ERP without validating the model carries high cost and risk. A mock-data POC lets stakeholders experience the proposed solution, confirm the information architecture, and make an informed decision about further investment — at a fraction of the cost and time.

---

## 3. Stakeholders

| Stakeholder | Role / Interest | Involvement in POC |
|---|---|---|
| Project Sponsor (Rural Development leadership) | Funds the initiative; approves scope and progression | Reviews demos; approves go/no-go |
| Product Owner | Owns requirements and priorities; arbitrates scope | Continuous; signs off on requirements |
| District / Zone Administrators | Primary future users; need asset visibility for their jurisdiction | Provide feedback; validate journeys |
| Panchayat Officials | Closest to the assets; future data contributors and consumers | Validate asset-level realism |
| Solution / Technical Architect | Ensures future-ready, data-source-agnostic design | Designs architecture; defines data layer |
| Development Team | Builds the POC against this documentation | Implements screens, logic, mock data |
| Business Analyst | Translates needs into requirements and documentation | Authors and maintains documents |
| QA / Reviewer | Validates against acceptance criteria | Verifies POC against success criteria |

> For the POC, the most important stakeholders are the **Product Owner** (scope authority) and the **Architect** (future-readiness authority).

---

## 4. Business Goals

| # | Business Goal | Linked POC Capability |
|---|---|---|
| BG-01 | Establish one authoritative repository of rural public assets | Asset Management, Mock Data layer |
| BG-02 | Reflect the real administrative hierarchy in the system | Hierarchy Navigation |
| BG-03 | Make asset location visible and accurate | Asset Location Management |
| BG-04 | Track and surface asset health and remaining life automatically | Asset Lifecycle Monitoring |
| BG-05 | Provide at-a-glance insight to decision makers | Dashboard Analytics |
| BG-06 | Allow fast retrieval of any asset | Search & Filter |
| BG-07 | Link photographic evidence to each asset | Asset Photo Management |
| BG-08 | Lay a foundation that can scale to production without rework | Future-ready architecture |

---

## 5. Functional Scope

The POC delivers the following functional capabilities. Each is detailed in `03-functional-requirements-specification.md`.

1. **Dashboard** — Total asset count, category count, zone-wise and panchayat-wise counts, lifecycle summary, asset health indicators, and drill-down navigation.
2. **Asset Category Management** — Browse and view the four asset categories and their sub-types.
3. **Asset Management** — View asset lists and complete asset detail records.
4. **Asset Location Management** — Display address, latitude, and longitude; render a map/coordinate view.
5. **Asset Photo Management** — Display photos associated with each asset in a gallery.
6. **Asset Lifecycle Monitoring** — Compute current age, remaining life, and health status (Healthy / Near Expiry / Expired).
7. **Search & Filter** — Search assets by attributes; filter lists by hierarchy, category, and status.
8. **Hierarchy Navigation** — Navigate State → District → Zone → Panchayat → Category → Asset, both top-down (drill) and via dashboard entry points.

### 5.1 Functional Scope Boundaries

- The POC presents and navigates data; it does **not** create, edit, or delete records through the UI in Phase 1 (data originates from mock JSON). Create/Edit flows are a documented future extension.
- All data is read from the mock data layer; no persistence occurs.

---

## 6. Non-Functional Scope

Even as a POC, RAMP targets a baseline of quality attributes. These are intentionally right-sized for a POC, not production SLAs.

| Attribute | POC Expectation | Future (Production) Direction |
|---|---|---|
| **Usability** | Clean, intuitive navigation; a new user can reach an asset detail unaided | Formal UX testing, accessibility (WCAG) compliance |
| **Performance** | Instant interactions on small in-memory mock data | Pagination, server-side filtering, caching at scale |
| **Maintainability** | Clear separation of UI / logic / data; reusable components | Same patterns hardened; documented APIs |
| **Portability / Future-readiness** | Data layer abstracted so APIs/DB can replace mock data with no UI change | Live API + database with identical contracts |
| **Responsiveness** | Layout adapts to desktop and tablet widths | Full responsive + native mobile app (out of scope now) |
| **Reliability** | Deterministic behavior on fixed mock data | Error handling, retries, monitoring against live services |
| **Security** | Basic; no sensitive data in mock set | AuthN/AuthZ, RBAC, audit, data protection |
| **Consistency** | Lifecycle status always derived, never stale | Same rule, enforced server-side |

> **Non-functional guiding rule:** Do not invest in production-grade non-functional engineering during the POC, but never make a design choice that *blocks* it later.

---

## 7. Assumptions

| # | Assumption |
|---|---|
| A-01 | Mock JSON data is the sole data source for Phase 1. |
| A-02 | The administrative hierarchy and asset categories provided are accurate and sufficient. |
| A-03 | Lifecycle thresholds (Healthy > 5y, Near Expiry ≤ 5y, Expired ≤ 0y) are agreed and fixed for the POC. |
| A-04 | "Current Year" for calculations is the runtime year. |
| A-05 | The client is a modern web browser; no native mobile build is required. |
| A-06 | Mock data volume is small enough to operate in-memory without pagination. |
| A-07 | UI write operations (create/update/delete) are out of scope for Phase 1. |
| A-08 | Stakeholder availability for feedback is sufficient throughout the POC. |

---

## 8. Constraints

| # | Constraint |
|---|---|
| C-01 | No database implementation in Phase 1. |
| C-02 | The system must not be built as a production ERP. |
| C-03 | The architecture must remain future-ready for API/DB migration with minimal change. |
| C-04 | Excluded modules must not be implemented in the POC. |
| C-05 | UI must never access mock data files directly; all access goes through a data/service layer. |
| C-06 | POC effort, timeline, and quality bar are sized for validation, not production launch. |

---

## 9. Future Scope

The following are explicitly deferred but anticipated. The POC architecture is designed so each can be added without re-platforming.

### 9.1 Data & Platform Evolution
- **Database implementation** — Replace mock JSON collections with relational tables (or equivalent); see `06-data-model-document.md` for the target logical model.
- **Live APIs** — Introduce REST/GraphQL services exposing the same resources the mock layer currently fakes.
- **Write operations** — Add create/update/delete flows for assets, categories, photos, and hierarchy nodes.

### 9.2 Functional Expansion
- **Maintenance Management** — Schedule and record maintenance against assets.
- **Inspection Management** — Capture inspection findings tied to lifecycle health.
- **Work Orders** — Generate and track work orders, especially for near-expiry/expired assets.
- **Notifications / Alerts** — Proactively warn officials about assets crossing health thresholds.
- **Approval Workflows** — Multi-level review and approval of asset changes.
- **Mobile Application** — Native field app for data capture (photos, GPS) at the asset.

### 9.3 Platform Capabilities
- **Authentication & Authorization** with RBAC aligned to the administrative hierarchy.
- **Reporting & Export** (PDF/Excel) and scheduled reports.
- **Audit Trails** for all data changes.
- **Advanced Analytics** — Trends, predictive lifecycle forecasting, geospatial heat maps.

### 9.4 Migration Readiness Summary
Because the POC keeps UI, business logic, and data access separate, the path from POC to production is incremental: introduce the database, stand up APIs that match the existing data contracts, swap the data provider, then layer on write operations and the deferred modules — each as an independent, low-risk step.
