# CLAUDE.md — Rural Asset Management Platform (RAMP)

> **This is the master instruction file for Claude Code.**
> Read this file **before doing anything** in this repository. It governs how RAMP is built. Every other file in `.claude/` expands on a section here. When any instruction conflicts, the order of precedence is: `BUSINESS_RULES.md` (for domain logic) → `LARAVEL_ARCHITECTURE.md` (for stack/structure) → `ARCHITECTURE_RULES.md` (technology-agnostic principles) → this file → other docs.

> ## ⚙️ Technology Stack (DECIDED) — Laravel 12 + Livewire 3
> RAMP is built with **Laravel 12 · PHP 8.2+ · Livewire 3 · Alpine · Tailwind v4**, charts via **ApexCharts**, maps via **Google Maps JS API**, data from **mock JSON only**. **Read `LARAVEL_ARCHITECTURE.md` before writing any code.** The architecture is: `Livewire → Service → Contract (seam) → Mock Provider → mock JSON` (future: `→ Repository → Eloquent → PostgreSQL`).
>
> **No React / Vue / TypeScript** — the frontend is **Blade + Livewire 3 + Alpine + Tailwind v4** only. The `.claude/` build guides (`ARCHITECTURE_RULES.md`, `DEVELOPMENT_RULES.md`) are **fully Laravel-native**. The original `docs/` set (notably `docs/10`) is the *technology-agnostic source* and may still show generic/React-shaped examples — when it does, the Laravel mapping in `LARAVEL_ARCHITECTURE.md` and the `.claude/` guides wins (screens→Livewire, hooks→component classes, `domain/`→`app/Services`+`app/Support`, data interface→`app/Contracts`, providers→`app/DataProviders`, factory→`DataLayerServiceProvider`). All **domain rules** (`BUSINESS_RULES.md`) and **UI rules** (`UI_RULES.md` + `UI_DESIGN_SYSTEM.md`) carry over unchanged. **Sprint 0 is complete** — see `LARAVEL_ARCHITECTURE.md` §5.
>
> ## 🎨 Premium UI — read `UI_DESIGN_SYSTEM.md` before building any screen
> The product must look **professional and premium**: a calm, data-first, enterprise aesthetic. `UI_DESIGN_SYSTEM.md` is the house style (refined neutral palette, soft layered elevation, Inter type scale, quiet motion, premium component specs). It sits **on top of** `UI_RULES.md`/`docs/11`, whose canonical **status colors and accessibility rules always win**. Design tokens live in `resources/css/app.css` (`@theme`) — never hard-code hex values in components; reference the token utilities. After editing Blade classes, run `npm run build` so Tailwind v4 rescans.

---

## 1. Project Overview

**RAMP (Rural Asset Management Platform)** is a centralized system for managing and monitoring **non-movable public assets** owned by Panchayat & Rural Development Departments — assets such as schools, health centres, water tanks, panchayat offices, and community halls.

The platform lets officials:

- See **how many assets exist**, of what kind, and **where** they are.
- Navigate assets through the **administrative hierarchy** (District → Zone → Panchayat → Asset Category → Asset). There is no State level — District is the root.
- Monitor each asset's **lifecycle health** (Healthy / Near Expiry / Expired / Unknown) based on its construction year and expected life.
- View asset **location, photos, and lifecycle** details.
- **Search and filter** assets quickly.

**What we are building right now is a Proof of Concept (POC).** The POC validates the concept, the user journeys, the lifecycle logic, and the dashboard's value — using **hard-coded mock JSON data only**. There is **no database and no backend API in Phase 1**. However, the POC must be architected so that it can later consume real APIs and a database **without major refactoring**.

> **Golden rule:** Build the POC as if the database and APIs already exist — only the *implementation* behind the data layer is mocked. The UI must never know it is talking to mock JSON.

---

## 2. Project Scope

### 2.1 In Scope (Phase 1 — POC)

The POC delivers **eight core modules** as **read-and-display** experiences:

1. **Dashboard** — totals, category/zone/panchayat breakdowns, lifecycle health summary, drill-down shortcuts.
2. **Asset Category Management** — view categories and their sub-types and counts.
3. **Asset Management** — view assets and full asset detail (five information groups).
4. **Asset Location Management** — view asset coordinates/address on a map; handle "location unavailable".
5. **Asset Photo Management** — view asset photo galleries; handle "no photos" and broken-image placeholders.
6. **Asset Lifecycle Monitoring** — compute and display age, remaining life, and status.
7. **Search & Filter** — search by name/number; filter by zone, panchayat, category/type, and status.
8. **Hierarchy Navigation** — drill down and back up the administrative tree with breadcrumbs.

### 2.2 Out of Scope (Phase 1 — explicitly excluded)

Do **not** build any of the following in the POC:

- **Database / persistence** of any kind (SQLite, Postgres, Mongo, ORM, local storage as a datastore).
- **Backend API** implementation.
- **UI write operations** — no create, edit, or delete.
- **Maintenance Management**, **Inspection Management**, **Work Orders**, **Notifications**, **Approval Workflows**.
- **Native mobile app**.
- Production **authentication / authorization / RBAC**, audit logging, or export/reporting engines.

These are deferred to later phases (see §9 and `IMPLEMENTATION_STRATEGY.md`).

---

## 3. POC Constraints (Non-Negotiable)

These constraints are absolute. Violating any of them is a defect.

1. **Mock JSON only.** All data comes from `mock-data/*.json`. No database, no ORM, no persistence engine.
2. **No database implementation** in Phase 1.
3. **No backend API implementation** in Phase 1.
4. **No UI write operations** (create / edit / delete) in Phase 1 — the POC reads and displays.
5. **Future-ready data access.** Every data access goes through a single, stable abstraction (the data/service layer) so the mock provider can be swapped for an API client later with **zero UI changes**.
6. **Lifecycle status is always computed, never stored or hard-coded.** Only the inputs (`construction_year`, `expected_life`) are stored; status is derived at runtime by one shared function.
7. **Do not build excluded modules** (see §2.2).
8. **Do not over-engineer** toward a production ERP — but never make a choice that *blocks* production later.
9. **UI never reads JSON directly** and never calls a network directly — it depends only on the data-service interface.
10. **No hard-coded totals or counts** anywhere — all figures derive from the live (mock) dataset.

> **Self-check:** If swapping `MockDataProvider` for `ApiDataProvider` would force edits to any screen or component, the architecture has been violated — fix the abstraction, not the screen.

---

## 4. Business Context

**Who uses it:** District/Zone/Panchayat-level officials and administrators of the Panchayat & Rural Development Department who need a single, reliable view of public assets and their condition.

**The problem it solves:** Public asset information is typically scattered, inconsistent, and lacks a clear view of asset *condition over time*. RAMP centralizes asset inventory and makes **lifecycle health visible at a glance**, so officials can identify assets that are near expiry or expired and plan accordingly.

**The core mental model the product must convey:**

- Assets live inside a strict **administrative tree** (District → Zone → Panchayat → Asset Category → Asset).
- Every asset has a **computed health status** derived from how old it is versus how long it was expected to last.
- The **Dashboard** answers three questions immediately: *How many assets? Where are they? Which need attention?*
- Every aggregate number is a **doorway** that drills down to the underlying assets.

**Canonical example path:**
`Salem → North Zone → Erumapalayam Panchayat → Primary School → Government Primary School`

Full domain detail is in `PROJECT_CONTEXT.md`. Full rules are in `BUSINESS_RULES.md`.

---

## 5. Modules (Build Reference)

| # | Module | POC Behavior | Primary Rules |
|---|---|---|---|
| 1 | Dashboard | Show totals, breakdowns (zone/panchayat/category), health summary; every metric drills down | BR-NV-06, BR-HL-*, BR-CT-03 |
| 2 | Asset Category Management | List categories + sub-types + counts; zero-count categories still shown | BR-CT-01..04 |
| 3 | Asset Management | List assets; asset detail with 5 info groups (admin, asset, location, lifecycle, media) | BR-LC-*, BR-HL-* |
| 4 | Asset Location Management | Show pin/coordinates/address; "location unavailable" when missing/invalid | BR-LO-01..04 |
| 5 | Asset Photo Management | Photo gallery; ordering by `sequence`; placeholders; "no photos" empty state | BR-PH-01..04 |
| 6 | Asset Lifecycle Monitoring | Compute age, remaining life, status; show figures and badge | BR-LC-*, BR-HL-* |
| 7 | Search & Filter | Search name/number; filter zone/panchayat/category/status; AND logic; chips; reset | BR-SR-*, BR-FL-* |
| 8 | Hierarchy Navigation | Drill down/up the tree; breadcrumbs everywhere below dashboard | BR-NV-01..10 |

The **Asset List** is the **convergence screen** — reachable by full drill-down, by dashboard shortcuts, and by search. It must render consistently regardless of entry path.

---

## 6. Development Principles

1. **Future-ready over feature-rich.** The single most important property of the POC is that it can migrate to APIs + database with no UI rewrite. Architecture choices win over shortcuts.
2. **One source of truth per concept.** Lifecycle status, counts, thresholds, and hierarchy order are each defined in exactly one place and reused everywhere.
3. **Strict separation of concerns.** Data fetching, business logic, view orchestration, and presentation live in distinct layers (see `ARCHITECTURE_RULES.md`).
4. **Compute, don't store, derived values.** Especially lifecycle status — always computed at runtime.
5. **Reuse, don't duplicate.** Shared UI primitives (cards, tables, status badges, breadcrumbs, empty states) are built once and reused.
6. **Handle every edge state.** No photos, no coordinates, no results, unknown status, missing optional fields — all handled gracefully, never a crash or blank screen.
7. **Consistency is a feature.** Status colors, navigation patterns, spacing, and terminology are uniform across the app.
8. **Clarity over cleverness.** Code is read by future sessions (human and AI). Favor explicit, well-named, domain-aligned code.
9. **Test the rules.** Unit-test lifecycle boundaries and aggregation counts so behavior stays correct through refactors and the future provider swap.

---

## 7. Documentation References

The authoritative project documents live in `docs/`. Read the relevant ones **before** implementing a related area.

| Doc | File | Read it before… |
|---|---|---|
| 01 | `docs/01-project-vision.md` | Understanding goals, success criteria, scope |
| 02 | `docs/02-business-requirements-document.md` | Understanding stakeholders and business goals |
| 03 | `docs/03-functional-requirements-specification.md` | Implementing any module (FR-* + acceptance criteria) |
| 04 | `docs/04-screen-flow-document.md` | Building screens and navigation flows |
| 05 | `docs/05-wireframe-document.md` | Laying out any screen |
| 06 | `docs/06-data-model-document.md` | Defining types/entities and data shapes |
| 07 | `docs/07-business-rules.md` | Implementing **any** business logic (single source of truth) |
| 08 | `docs/08-mock-data-specification.md` | Authoring `mock-data/*.json` |
| 09 | `docs/09-development-roadmap.md` | Sequencing work / sprints |
| 10 | `docs/10-claude-development-guide.md` | Writing **any** code (architecture, folders, standards) |
| 11 | `docs/11-ui-ux-guidelines.md` | Any UI/styling/responsive work |
| 12 | `docs/12-system-flow-diagrams.md` | Understanding journeys and architecture visually |

The `.claude/` files are the **condensed, build-optimized** version of these docs:

- `PROJECT_CONTEXT.md` — domain model and concepts (from Docs 01, 04, 06, 07).
- `ARCHITECTURE_RULES.md` — architecture, layering, future migration (from Doc 10, 06).
- `DEVELOPMENT_RULES.md` — coding, folders, naming, components, errors (from Doc 10).
- `UI_RULES.md` — layout, dashboard, cards, tables, navigation, responsive, a11y (from Doc 11).
- `BUSINESS_RULES.md` — developer-formatted rules (from Doc 07).
- `IMPLEMENTATION_STRATEGY.md` — sequence, sprints, priorities, tech-debt avoidance (from Doc 09).

> If a `.claude/` file and a `docs/` file ever disagree, treat it as a documentation bug and prefer `docs/07-business-rules.md` for rules and `docs/10-claude-development-guide.md` for architecture, then flag the inconsistency.

---

## 8. Important Warnings

⚠️ **Do NOT add a database.** No SQLite/Postgres/Mongo/ORM/persistence. Mock JSON only.

⚠️ **Do NOT implement a backend API.** The data layer reads local JSON in Phase 1.

⚠️ **Do NOT build create/edit/delete UI.** The POC is read-only.

⚠️ **Do NOT store lifecycle status.** Compute it at runtime via the shared lifecycle service. Storing it (in JSON, state, or anywhere) is a defect.

⚠️ **Do NOT read `mock-data/*.json` from a screen or component.** Only the data layer touches JSON. UI depends on the `AssetDataService` interface.

⚠️ **Do NOT compute status or counts inside components.** Use the shared `domain/` services. Duplicated logic is a defect.

⚠️ **Do NOT hard-code totals/counts.** All numbers derive from the live dataset.

⚠️ **Do NOT build excluded modules** (maintenance, inspections, work orders, notifications, approvals, native mobile).

⚠️ **Do NOT over-engineer** toward a production ERP — but never make a choice that blocks the future API/DB migration.

⚠️ **Do NOT invent new status colors or navigation patterns.** Use the canonical ones in `UI_RULES.md`.

---

## 9. Future Roadmap

The POC is **Phase 1** of a five-phase plan. Phase 1 is structured so later phases are **incremental, low-risk additions** — not rewrites.

| Phase | Name | Goal | Data Source |
|---|---|---|---|
| **Phase 1** | **POC (now)** | Validate concept, journeys, lifecycle logic, dashboard value | **Mock JSON** |
| Phase 2 | API Enablement | Replace mock provider with live read APIs — **same UI** | API (read) |
| Phase 3 | Database & Persistence | Introduce database; enable write operations (create/edit/delete) | Database via APIs |
| Phase 4 | Functional Expansion | Add deferred modules (maintenance, inspections, work orders, notifications, approvals) | Database/APIs |
| Phase 5 | Hardening & Mobile | AuthN/AuthZ + RBAC, reporting/export, audit, native mobile app | Production stack |

**The migration promise:** When Phase 2 begins, we implement `ApiDataProvider` against the **same** `AssetDataService` interface, switch the provider via configuration, and verify **zero UI changes** were required. That verification is the proof the POC was built correctly.

See `IMPLEMENTATION_STRATEGY.md` for the sprint-level plan and `ARCHITECTURE_RULES.md` §"Future Database Migration Rules" for the migration mechanics.

---

*End of CLAUDE.md — read the referenced `.claude/` files next, starting with `PROJECT_CONTEXT.md` and `ARCHITECTURE_RULES.md`.*
