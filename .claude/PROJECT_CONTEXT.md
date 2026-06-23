# PROJECT_CONTEXT.md — RAMP Domain Context

> Domain knowledge Claude Code needs to build RAMP correctly. This file describes **what** the system represents. For **how** to build it, see `ARCHITECTURE_RULES.md` and `DEVELOPMENT_RULES.md`; for domain **rules**, see `BUSINESS_RULES.md`.

---

## 1. Project Summary

**RAMP (Rural Asset Management Platform)** centralizes the inventory and lifecycle monitoring of **non-movable public assets** managed by Panchayat & Rural Development Departments — schools, health centres, water tanks, panchayat offices, and community halls.

The product gives officials a single place to answer:

- **How many** public assets exist, and of what kind?
- **Where** are they (administratively and geographically)?
- **Which** assets are healthy, nearing the end of their useful life, or already expired?

This build is a **Proof of Concept**: it runs entirely on **mock JSON data**, with **no database and no backend API**, but is architected so it can later move to real APIs and a database with no UI rewrite. The POC is **read-only** — it displays data; it does not create, edit, or delete it.

The product's job is to make **asset inventory** and **asset condition over time** visible and navigable.

---

## 2. Administrative Hierarchy

RAMP organizes every asset within a strict **administrative tree**. The order is fixed and is the backbone of navigation, breadcrumbs, and filtering.

```
District
└── Zone
    └── Panchayat
        └── Asset Category
            └── Asset
```

> **Note:** There is **no State** level. The hierarchy starts at **District** — District is the top of the administrative tree throughout the POC (data, navigation, breadcrumbs, filtering).

| Level | Meaning | Example |
|---|---|---|
| District | Top-level administrative region | Salem |
| Zone | An administrative zone within a district | North Zone |
| Panchayat | A panchayat within a zone (owns assets) | Erumapalayam Panchayat |
| Asset Category | Classification of assets at a panchayat | Primary School |
| Asset | A specific physical asset | Government Primary School |

**Canonical example path:**
`Salem → North Zone → Erumapalayam Panchayat → Primary School → Government Primary School`

**Structural facts (must hold in data and code):**

- The hierarchy is a **strict tree**: every child references exactly **one** parent (`Zone.district_id`, `Panchayat.zone_id`, `Asset.panchayat_id`). District is the root and has no parent.
- Assets are also classified by **Asset Category** (`Asset.category_id`) — a separate 1:N relationship that coexists with the location tree.
- Navigation never crosses to an unrelated parent; drill-down always follows valid parent→child links.
- Identifiers (`id`) are stable keys used as foreign keys between entities (mirroring future DB primary/foreign keys).

> Entity field-level details (types, required fields, relationships) are in `docs/06-data-model-document.md`. Field naming is `snake_case` for forward-compatibility with a future database.

---

## 3. User Journey

The **canonical journey** moves from the Dashboard down the hierarchy to a single asset, then into that asset's sub-views. **Search/Filter** offers a shortcut straight to the Asset List.

```
Dashboard
   ↓ (drill down, or click a KPI / metric / status)
Zone View
   ↓
Panchayat View
   ↓
Asset Category View
   ↓
Asset List  ← also reachable directly via Search / Filter and via Dashboard shortcuts
   ↓ (select an asset)
Asset Detail
   ├── Photo Gallery
   ├── Location / Map View
   └── Lifecycle View
```

**Journey principles:**

- **Drill-down, never dead-end.** Every aggregate on the Dashboard is clickable and drills into a filtered Asset List.
- **Always reversible.** Breadcrumbs let users step back up the hierarchy from any screen below the Dashboard — no reliance on the browser back button.
- **Convergence at the Asset List.** Multiple paths (by zone, by panchayat, by category, by search, by dashboard shortcut) all lead to the same Asset List, which renders identically regardless of entry path.
- **Sub-views return to detail.** Photos, Location, and Lifecycle are sub-views of an asset and always return to Asset Detail.

> The visual flow diagrams for these journeys are in `docs/12-system-flow-diagrams.md`. Screen-by-screen specs are in `docs/04-screen-flow-document.md`.

---

## 4. Asset Categories

Every asset belongs to **exactly one category** and **exactly one asset type (sub-type)** within that category. The POC uses these four categories and their sub-types:

| Category | `id` (mock) | Sub-types (asset_type) |
|---|---|---|
| **Educational Assets** | `CAT-EDU` | Primary School, Nursery School, Government School |
| **Healthcare Assets** | `CAT-HLT` | Primary Health Centre, Rural Health Facility |
| **Water Infrastructure** | `CAT-WAT` | Overhead Water Tank, Underground Water Tank, Bore Well |
| **Public Infrastructure** | `CAT-PUB` | Panchayat Office, Community Hall |

**Categorization rules (summary):**

- An asset's `asset_type` **must** be a valid sub-type of its `category_id`.
- Category counts must **reconcile**: the sum of per-category asset counts equals the total asset count.
- A category with **zero assets** is still displayed with a count of 0 (never hidden).

**Asset numbering convention** (from mock data): category-prefixed, e.g. `EDU-0001`, `HLT-0001`, `WAT-0001`, `PUB-0001`. `asset_number` is unique across all assets.

> Full category rules: `BR-CT-01..04` in `BUSINESS_RULES.md`. Mock data for categories/assets: `docs/08-mock-data-specification.md`.

---

## 5. Dashboard Concept

The **Dashboard is the landing screen and command center.** It must answer, at a glance: *How many? Where? What needs attention?*

**What the Dashboard shows (top to bottom):**

1. **KPI Summary Row** — headline counts: **Total Assets**, **Asset Categories**, **Zones**, **Panchayats**.
2. **Asset Health Summary** — distribution across **Healthy / Near Expiry / Expired / Unknown**, using the canonical status colors.
3. **Breakdown Cards** — **Zone-wise** and **Panchayat-wise** asset counts.
4. **Category Breakdown** — assets per category.
5. **Drill-down entry points** — every number is a doorway into a filtered Asset List.

**Dashboard principles:**

- **Every aggregate is clickable.** Clicking a KPI, a health segment, or a breakdown row navigates to the Asset List filtered by that dimension (zone, panchayat, category, or status).
- **Status at a glance.** Health is shown with consistent color **and** label (never color alone).
- **Surface incomplete data.** Assets with **Unknown** status are shown separately, not hidden.
- **All numbers are computed** from the live dataset by the shared aggregation service — never hard-coded.

**Aggregation expectations** (counts like total assets, per-category, per-zone, per-panchayat, and per-status) come from the `domain/aggregation` service over the mock dataset; the expected seed totals are documented in `docs/08-mock-data-specification.md`.

> Dashboard layout and card/table standards: `UI_RULES.md`. Drill-down rule: `BR-NV-06`.

---

## 6. Lifecycle Concept

Lifecycle health is the **signature feature** of RAMP. It tells officials which assets need attention. It is **always computed at runtime** from two stored inputs — never stored.

**Stored inputs (per asset):**

- `construction_year` — the year the asset was built.
- `expected_life` — the number of years the asset was expected to last.

**Computed values:**

```
Current Age    = Current Year − construction_year
Remaining Life = expected_life − Current Age
```

**Status (derived from Remaining Life):**

| Status | Condition | Color |
|---|---|---|
| **Healthy** | Remaining Life **> 5** years | Green |
| **Near Expiry** | **0 < Remaining Life ≤ 5** years | Amber |
| **Expired** | Remaining Life **≤ 0** | Red |
| **Unknown** | inputs missing/invalid | Grey |

**Decision order (first match wins):**

```
1. inputs missing/invalid?   → Unknown
2. Remaining Life ≤ 0?        → Expired
3. Remaining Life ≤ 5?        → Near Expiry   (covers 0 < RL ≤ 5)
4. otherwise (RL > 5)         → Healthy
```

**Boundaries (must be implemented exactly):**

- Remaining Life **exactly 5** → **Near Expiry** (the ≤ 5 boundary is inclusive).
- Remaining Life **exactly 0** → **Expired** (the ≤ 0 boundary is inclusive).

**Worked examples (Current Year = 2026):**

| construction_year | expected_life | Current Age | Remaining Life | Status |
|---|---|---|---|---|
| 2010 | 30 | 16 | 14 | Healthy |
| 2016 | 15 | 10 | 5 | Near Expiry |
| 2000 | 25 | 26 | −1 | Expired |
| 2021 | 5 | 5 | 0 | Expired |
| (missing) | 30 | — | — | Unknown |
| 2010 | (missing) | — | — | Unknown |

**Critical implementation notes:**

- **One shared function** computes age, remaining life, and status for **all** consumers (dashboard, lists, detail, lifecycle view). No inline duplication.
- Status is **recomputed on each load** — never cached as a stored value.
- Thresholds (e.g., `NEAR_EXPIRY_YEARS = 5`) are defined **once**, not as magic numbers.
- A future construction year is **invalid** → Unknown; `expected_life` must be a positive integer.

> Full lifecycle and health rules: `BR-LC-*` and `BR-HL-*` in `BUSINESS_RULES.md`.

---

## 7. Navigation Concept

Navigation mirrors the administrative tree and is built on **hierarchical drill-down** with **breadcrumbs** as the primary wayfinding mechanism.

**How navigation works:**

- **Drill down** the tree: Dashboard → Zone → Panchayat → Asset Category → Asset List → Asset Detail → (Photos | Location | Lifecycle).
- **Drill-down carries context.** Each step accumulates the selected district/zone/panchayat/category/status and passes it to the destination screen.
- **Breadcrumbs reflect a valid path** from the top of the hierarchy to the current location. Selecting a breadcrumb segment navigates to that ancestor and drops deeper context appropriately.
- **Dashboard metrics are shortcuts.** They jump directly to a filtered Asset List carrying the metric's filter (zone, panchayat, category, or status).
- **Sub-views return to detail.** Photos, Location, and Lifecycle always return to their parent Asset Detail.
- **No dead-ends.** A level with no children/records shows an **empty state** and a way back — never an error.
- **Invalid identifiers degrade gracefully.** An unknown/invalid node id redirects to the nearest valid ancestor with an informational message.
- **Breadcrumbs appear on every screen below the Dashboard.** The Dashboard itself is "Home" and shows no breadcrumb.

**Breadcrumb format:**

```
Home / Salem / North Zone / Erumapalayam Panchayat / Primary School / Govt Primary School
```

- First crumb is always **Home** (links to Dashboard).
- Every crumb except the last is a link; the last (current screen) is plain text.
- On narrow screens, long trails collapse to `Home / … / Current`, preserving first and last.

> Full navigation rules: `BR-NV-01..10` in `BUSINESS_RULES.md`. Navigation/breadcrumb UI standards: `UI_RULES.md`.

---

## 8. Quick Domain Glossary

| Term | Meaning |
|---|---|
| **Asset** | A specific non-movable public asset (e.g., a school building). |
| **Asset Number** | Unique, category-prefixed identifier (e.g., `EDU-0001`). |
| **Asset Category** | Classification (Educational, Healthcare, Water, Public Infrastructure). |
| **Asset Type / Sub-type** | A specific kind within a category (e.g., Primary School). |
| **Panchayat** | Local administrative unit that owns assets. |
| **Zone / District** | Higher levels of the administrative tree (District is the root). |
| **Current Age** | Current Year − construction_year. |
| **Expected Life** | Years the asset was expected to last (stored input). |
| **Remaining Life** | Expected Life − Current Age (computed). |
| **Status / Health** | Computed condition: Healthy / Near Expiry / Expired / Unknown. |
| **Dashboard** | Landing command center with totals, breakdowns, and health summary. |
| **Asset List** | The convergence screen listing assets; reachable from many paths. |
| **Data/Service Layer** | The abstraction the UI depends on to get data (mock now, API later). |

---

*End of PROJECT_CONTEXT.md*
