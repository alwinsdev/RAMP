# Business Rules — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-07 |
| Document Title | Business Rules |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Product Owner, Developers, QA |
| Related Documents | 03-FRS, 06-Data Model, 08-Mock Data |

---

## 1. Introduction

This document is the **single source of truth for business rules** in the RAMP POC. Where any other document references a rule, it defers to this one. Rules are grouped by domain: lifecycle calculations, asset health, navigation, search, filtering, and data integrity. Each rule has an ID (`BR-<DOMAIN>-<NN>`) for traceability.

A central design principle: **derived values (especially lifecycle status) are computed, never stored.** This keeps the system correct as time passes and as data changes.

---

## 2. Lifecycle Calculation Rules

These rules govern how an asset's age and remaining life are computed from stored inputs.

| ID | Rule |
|---|---|
| BR-LC-01 | **Current Year** is the system/runtime year at the moment of computation. |
| BR-LC-02 | **Current Age** = `Current Year − Construction Year`. |
| BR-LC-03 | **Remaining Life** = `Expected Life − Current Age`. |
| BR-LC-04 | Lifecycle figures must be recomputed on each load/use; they are never cached as fixed stored values. |
| BR-LC-05 | A single shared computation function/service produces these figures for all consumers (dashboard, lists, detail). |
| BR-LC-06 | If `Construction Year` or `Expected Life` is missing or invalid, lifecycle figures are not computed; the asset's status is **Unknown** (see health rules). |
| BR-LC-07 | `Construction Year` must be ≤ Current Year; a future construction year is treated as invalid (→ Unknown). |
| BR-LC-08 | `Expected Life` must be a positive integer; zero or negative is invalid (→ Unknown). |
| BR-LC-09 | Negative `Remaining Life` is a valid result and indicates the asset is past its expected life. |

**Worked examples (Current Year = 2026):**

| Construction Year | Expected Life | Current Age | Remaining Life | Status |
|---|---|---|---|---|
| 2010 | 30 | 16 | 14 | Healthy |
| 2016 | 15 | 10 | 5 | Near Expiry |
| 2000 | 25 | 26 | −1 | Expired |
| 2021 | 5 | 5 | 0 | Expired |
| — | 30 | — | — | Unknown |
| 2010 | — | — | — | Unknown |

---

## 3. Asset Health Rules

These rules map computed `Remaining Life` to a health status.

| ID | Rule |
|---|---|
| BR-HL-01 | **Healthy**: `Remaining Life > 5` years. |
| BR-HL-02 | **Near Expiry**: `0 < Remaining Life ≤ 5` years. |
| BR-HL-03 | **Expired**: `Remaining Life ≤ 0` years. |
| BR-HL-04 | **Unknown**: lifecycle inputs missing/invalid (per BR-LC-06/07/08). |
| BR-HL-05 | **Boundary — exactly 5:** `Remaining Life == 5` → **Near Expiry** (the ≤ 5 boundary is inclusive). |
| BR-HL-06 | **Boundary — exactly 0:** `Remaining Life == 0` → **Expired** (the ≤ 0 boundary is inclusive). |
| BR-HL-07 | Health status is presented with a consistent visual encoding: Healthy = green, Near Expiry = amber/yellow, Expired = red, Unknown = neutral/grey. |
| BR-HL-08 | Assets with **Unknown** status are excluded from health percentage calculations but counted separately as "Incomplete/Unknown". |
| BR-HL-09 | The same status value is used everywhere (dashboard summary, list badge, detail, lifecycle view) — there is exactly one definition. |

**Status decision order (evaluate top to bottom, first match wins):**
```
1. inputs missing/invalid?      → Unknown
2. Remaining Life <= 0?         → Expired
3. Remaining Life <= 5?         → Near Expiry   (covers 0 < RL <= 5)
4. otherwise (Remaining Life>5) → Healthy
```

---

## 4. Navigation Rules

These rules govern movement through the administrative hierarchy and between screens.

| ID | Rule |
|---|---|
| BR-NV-01 | The hierarchy order is fixed: **State → District → Zone → Panchayat → Asset Category → Asset**. |
| BR-NV-02 | Each child node belongs to exactly one parent; navigation never crosses to an unrelated parent. |
| BR-NV-03 | Drill-down carries accumulated context (selected state/district/zone/panchayat/category/status) to the destination screen. |
| BR-NV-04 | A breadcrumb trail always reflects a valid path from the top of the hierarchy to the current location. |
| BR-NV-05 | Selecting a breadcrumb segment navigates to that ancestor level and drops deeper context appropriately. |
| BR-NV-06 | Dashboard metrics act as navigation shortcuts that jump directly to a filtered Asset List carrying the metric's filter (zone, panchayat, category, or status). |
| BR-NV-07 | Sub-views (Photos, Location, Lifecycle) always return to their parent Asset Detail. |
| BR-NV-08 | A level with no children/records shows an empty state and a way back — never a dead end or error. |
| BR-NV-09 | Unknown/invalid node identifiers redirect to the nearest valid ancestor with an informational message. |
| BR-NV-10 | The Asset List is the convergence screen reachable by full drill-down, dashboard shortcuts, and search; it must render consistently regardless of entry path. |

---

## 5. Search Rules

These rules govern free-text search of assets.

| ID | Rule |
|---|---|
| BR-SR-01 | Search matches against **Asset Name** and **Asset Number**. |
| BR-SR-02 | Search is **case-insensitive**. |
| BR-SR-03 | Leading/trailing whitespace in the query is trimmed before matching. |
| BR-SR-04 | Matching is substring/contains based (e.g., "school" matches "Govt Primary School"). |
| BR-SR-05 | An empty query returns the full set governed by the currently active filters. |
| BR-SR-06 | Search combines with active filters using **AND** logic (a result must satisfy both the query and all filters). |
| BR-SR-07 | A query with no matches shows a clear "no results" empty state, not an error. |
| BR-SR-08 | Search operates over data from the data/service layer (in-memory for the POC; server-side query in future, with the same observable behavior). |
| BR-SR-09 | The result count reflects the number of assets matching the combined search + filter state. |

---

## 6. Filtering Rules

These rules govern structured filtering of asset lists.

| ID | Rule |
|---|---|
| BR-FL-01 | Available filters: **Zone**, **Panchayat**, **Category/Type**, **Lifecycle Status**. |
| BR-FL-02 | Multiple **different** filters combine with **AND** (e.g., Zone = North AND Status = Near Expiry). |
| BR-FL-03 | Multiple values within a **single** filter (where multi-select is supported) combine with **OR**. |
| BR-FL-04 | The **Panchayat** filter options are constrained by the selected **Zone** (hierarchy-aware). When Zone changes, incompatible Panchayat selections are cleared. |
| BR-FL-05 | The **Lifecycle Status** filter uses **computed** status values (Healthy/Near Expiry/Expired/Unknown). |
| BR-FL-06 | Filters applied via drill-down or dashboard shortcut are reflected as active filter chips and can be individually removed. |
| BR-FL-07 | **Reset** clears all filters and search, restoring the full list for the current hierarchy context. |
| BR-FL-08 | No assets matching the active filters shows the "no results" empty state. |
| BR-FL-09 | Filter state and result count update together and remain consistent. |
| BR-FL-10 | Filtering operates over data from the data/service layer with identical behavior in mock and future API modes. |

---

## 7. Categorization Rules

| ID | Rule |
|---|---|
| BR-CT-01 | Every asset belongs to exactly one category and exactly one asset type (sub-type) within that category. |
| BR-CT-02 | An asset's `asset_type` must be a valid sub-type of its category. |
| BR-CT-03 | Category counts must reconcile: the sum of per-category asset counts equals the total asset count. |
| BR-CT-04 | A category with zero assets is still displayed with a count of 0 (not hidden). |

---

## 8. Location Rules

| ID | Rule |
|---|---|
| BR-LO-01 | When present, `latitude` ∈ [−90, 90] and `longitude` ∈ [−180, 180]. |
| BR-LO-02 | Missing/invalid coordinates do not block display of the rest of the asset record. |
| BR-LO-03 | If coordinates are missing/invalid, the map shows a "location unavailable" state; the address is still shown if present. |
| BR-LO-04 | Address is optional; its absence is handled gracefully. |

---

## 9. Photo Rules

| ID | Rule |
|---|---|
| BR-PH-01 | Each photo is associated with exactly one asset. |
| BR-PH-02 | Photo display order is deterministic (by `sequence`, else by load order). |
| BR-PH-03 | A missing/broken image source renders a placeholder, not a broken element. |
| BR-PH-04 | An asset with no photos shows an empty state, not an error. |

---

## 10. Data Integrity Rules (Runtime)

These complement the logical integrity rules in `06-data-model-document.md` and are applied as light runtime validation in the POC.

| ID | Rule |
|---|---|
| BR-DI-01 | `asset_number` is unique; duplicates are a data error surfaced during development. |
| BR-DI-02 | Every asset references a valid panchayat and category; orphan references are invalid. |
| BR-DI-03 | Every hierarchy node references a valid parent. |
| BR-DI-04 | Every photo references a valid asset. |
| BR-DI-05 | Counts displayed anywhere must derive from the live dataset; no hard-coded totals. |

---

## 11. Rule Precedence & Consistency

| ID | Rule |
|---|---|
| BR-PR-01 | This document overrides any conflicting rule statement elsewhere; other documents defer here. |
| BR-PR-02 | There is exactly one implementation of each calculation (lifecycle, status) reused across the application. |
| BR-PR-03 | Status thresholds, hierarchy order, and filter semantics are centralized so a future change is made in one place. |
| BR-PR-04 | All rules are written to hold identically whether data comes from mock JSON or future APIs/database. |

---

## 12. Business Rules Traceability (Summary)

| Domain | Rule IDs | Primary Consumers |
|---|---|---|
| Lifecycle Calculation | BR-LC-01…09 | Lifecycle Monitoring, Dashboard, Lists, Detail |
| Asset Health | BR-HL-01…09 | Dashboard health summary, status badges |
| Navigation | BR-NV-01…10 | Hierarchy Navigation, breadcrumbs, drill-down |
| Search | BR-SR-01…09 | Search & Filter, Asset List |
| Filtering | BR-FL-01…10 | Search & Filter, Asset List |
| Categorization | BR-CT-01…04 | Asset Category Management |
| Location | BR-LO-01…04 | Asset Location Management |
| Photo | BR-PH-01…04 | Asset Photo Management |
| Data Integrity | BR-DI-01…05 | All modules |
| Precedence | BR-PR-01…04 | Governance |
