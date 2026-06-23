# BUSINESS_RULES.md — RAMP

> The **single source of truth for domain logic**, formatted for implementation. Derived from `docs/07-business-rules.md`. Where any instruction conflicts on domain logic, **this file wins**. Each rule keeps its original ID (`BR-<DOMAIN>-<NN>`) for traceability back to `docs/07`.

**Two principles that govern everything here:**

1. **Derived values are computed, never stored** — especially lifecycle status.
2. **One implementation per calculation**, reused everywhere (no inline duplication).

---

## 1. Lifecycle Calculation Rules

Govern how an asset's age and remaining life are computed from stored inputs (`construction_year`, `expected_life`).

| ID | Rule (developer form) |
|---|---|
| BR-LC-01 | `currentYear` = the system/runtime year **at the moment of computation** (not a stored constant). |
| BR-LC-02 | `currentAge = currentYear − construction_year`. |
| BR-LC-03 | `remainingLife = expected_life − currentAge`. |
| BR-LC-04 | **Recompute on every load/use.** Never cache these as fixed stored values. |
| BR-LC-05 | A **single shared function/service** produces these figures for all consumers (dashboard, lists, detail, lifecycle view). |
| BR-LC-06 | If `construction_year` **or** `expected_life` is missing or invalid → do **not** compute figures; status is **Unknown**. |
| BR-LC-07 | `construction_year` must be **≤ currentYear**. A future year is invalid → Unknown. |
| BR-LC-08 | `expected_life` must be a **positive integer** (> 0). Zero or negative is invalid → Unknown. |
| BR-LC-09 | A **negative `remainingLife` is valid** and means the asset is past its expected life (→ Expired). |

**Reference implementation (pseudocode):**

```
const NEAR_EXPIRY_YEARS = 5   // BR-HL-01/02 threshold — defined once, no magic numbers

function computeLifecycle(asset, currentYear = systemYear()):
    // ---- validate inputs (BR-LC-06/07/08) ----
    cy = asset.construction_year
    el = asset.expected_life
    if cy is missing or not an integer:           return { status: "Unknown" }
    if el is missing or not an integer or el <= 0: return { status: "Unknown" }
    if cy > currentYear:                           return { status: "Unknown" }   // future build invalid

    // ---- compute (BR-LC-02/03) ----
    currentAge    = currentYear - cy
    remainingLife = el - currentAge               // negative is valid (BR-LC-09)

    // ---- derive status (see §2 decision order) ----
    status = statusFromRemainingLife(remainingLife)
    return { currentAge, remainingLife, status }
```

**Worked examples (currentYear = 2026):**

| construction_year | expected_life | currentAge | remainingLife | status |
|---|---|---|---|---|
| 2010 | 30 | 16 | 14 | Healthy |
| 2016 | 15 | 10 | 5 | Near Expiry |
| 2000 | 25 | 26 | −1 | Expired |
| 2021 | 5 | 5 | 0 | Expired |
| (missing) | 30 | — | — | Unknown |
| 2010 | (missing) | — | — | Unknown |

---

## 2. Asset Status Rules

Map computed `remainingLife` to a health status. **Status is always computed, never stored.**

| ID | Rule |
|---|---|
| BR-HL-01 | **Healthy**: `remainingLife > 5`. |
| BR-HL-02 | **Near Expiry**: `0 < remainingLife ≤ 5`. |
| BR-HL-03 | **Expired**: `remainingLife ≤ 0`. |
| BR-HL-04 | **Unknown**: lifecycle inputs missing/invalid (per BR-LC-06/07/08). |
| BR-HL-05 | **Boundary — exactly 5:** `remainingLife == 5` → **Near Expiry** (≤ 5 is inclusive). |
| BR-HL-06 | **Boundary — exactly 0:** `remainingLife == 0` → **Expired** (≤ 0 is inclusive). |
| BR-HL-07 | Visual encoding is fixed: Healthy = **green**, Near Expiry = **amber**, Expired = **red**, Unknown = **grey** (see `UI_RULES.md` for hex). |
| BR-HL-08 | Assets with **Unknown** status are **excluded from health percentages** but counted separately as "Incomplete/Unknown". |
| BR-HL-09 | The **same status value** is used everywhere (dashboard, list badge, detail, lifecycle view) — exactly one definition. |

**Decision order (first match wins) — implement exactly:**

```
function statusFromRemainingLife(remainingLife):
    // (inputs already validated; Unknown handled upstream in computeLifecycle)
    if remainingLife <= 0:  return "Expired"       // BR-HL-03/06 (covers 0)
    if remainingLife <= 5:  return "Near Expiry"   // BR-HL-02/05 (covers 0 < RL <= 5)
    return "Healthy"                               // BR-HL-01 (RL > 5)
```

Full status resolution including Unknown:

```
1. inputs missing/invalid?   → Unknown        (BR-HL-04)
2. remainingLife <= 0?       → Expired         (BR-HL-03/06)
3. remainingLife <= 5?       → Near Expiry     (BR-HL-02/05)
4. otherwise (RL > 5)        → Healthy         (BR-HL-01)
```

**The four status values are a fixed set:** `Healthy`, `Near Expiry`, `Expired`, `Unknown`. Do not introduce synonyms or new statuses.

---

## 3. Navigation Rules

Govern movement through the administrative hierarchy and between screens.

| ID | Rule |
|---|---|
| BR-NV-01 | Hierarchy order is fixed: **District → Zone → Panchayat → Asset Category → Asset**. There is no State level — District is the root. |
| BR-NV-02 | Each child belongs to exactly **one** parent; navigation never crosses to an unrelated parent. |
| BR-NV-03 | Drill-down **carries accumulated context** (selected district/zone/panchayat/category/status) to the destination screen. |
| BR-NV-04 | A breadcrumb trail always reflects a **valid path** from the top of the hierarchy to the current location. |
| BR-NV-05 | Selecting a breadcrumb segment navigates to that **ancestor level** and drops deeper context appropriately. |
| BR-NV-06 | **Dashboard metrics are navigation shortcuts** — they jump directly to a filtered Asset List carrying the metric's filter (zone, panchayat, category, or status). |
| BR-NV-07 | Sub-views (**Photos, Location, Lifecycle**) always return to their parent **Asset Detail**. |
| BR-NV-08 | A level with **no children/records** shows an **empty state and a way back** — never a dead end or error. |
| BR-NV-09 | **Unknown/invalid node identifiers** redirect to the nearest valid ancestor with an informational message. |
| BR-NV-10 | The **Asset List is the convergence screen** — reachable by full drill-down, dashboard shortcuts, and search; it must render consistently regardless of entry path. |

> UI/breadcrumb presentation rules are in `UI_RULES.md` §6. The navigation **logic and context-passing** are governed here.

---

## 4. Search Rules

Govern free-text search of assets.

| ID | Rule (developer form) |
|---|---|
| BR-SR-01 | Search matches against **`asset_name`** and **`asset_number`**. |
| BR-SR-02 | Search is **case-insensitive**. |
| BR-SR-03 | **Trim** leading/trailing whitespace from the query before matching. |
| BR-SR-04 | Matching is **substring/contains** (e.g., `"school"` matches `"Govt Primary School"`). |
| BR-SR-05 | An **empty query** returns the full set governed by the currently active filters. |
| BR-SR-06 | Search **combines with active filters using AND** (a result must satisfy the query **and** all filters). |
| BR-SR-07 | A query with **no matches** shows a clear **"no results"** empty state, not an error. |
| BR-SR-08 | Search operates over data from the **data/service layer** (in-memory for the POC; server-side query later, **same observable behavior**). |
| BR-SR-09 | The **result count** reflects the number of assets matching the combined search + filter state. |

**Reference matcher (pseudocode):**

```
function matchesQuery(asset, rawQuery):
    q = rawQuery.trim().toLowerCase()             // BR-SR-02/03
    if q == "": return true                        // BR-SR-05 (filters still apply elsewhere)
    name   = asset.asset_name.toLowerCase()
    number = asset.asset_number.toLowerCase()
    return name.contains(q) or number.contains(q)  // BR-SR-01/04
```

---

## 5. Filter Rules

Govern structured filtering of asset lists.

| ID | Rule |
|---|---|
| BR-FL-01 | Available filters: **Zone**, **Panchayat**, **Category/Type**, **Lifecycle Status**. |
| BR-FL-02 | Multiple **different** filters combine with **AND** (e.g., Zone = North **AND** Status = Near Expiry). |
| BR-FL-03 | Multiple values within a **single** filter (where multi-select is supported) combine with **OR**. |
| BR-FL-04 | The **Panchayat** filter options are constrained by the selected **Zone** (hierarchy-aware). When Zone changes, **clear** incompatible Panchayat selections. |
| BR-FL-05 | The **Lifecycle Status** filter uses **computed** status values (Healthy/Near Expiry/Expired/Unknown). |
| BR-FL-06 | Filters applied via drill-down or dashboard shortcut appear as **active filter chips** and can be **individually removed**. |
| BR-FL-07 | **Reset** clears all filters and search, restoring the full list for the current hierarchy context. |
| BR-FL-08 | **No matches** → the "no results" empty state. |
| BR-FL-09 | **Filter state and result count update together** and remain consistent. |
| BR-FL-10 | Filtering operates over the **data/service layer** with **identical behavior** in mock and future API modes. |

**Combined filtering model (pseudocode):**

```
function applyFilters(assets, filter):
    return assets.filter(asset =>
        (filter.zoneId      is empty or asset.zone_id      == filter.zoneId)        and  // BR-FL-02
        (filter.panchayatId is empty or asset.panchayat_id == filter.panchayatId)   and
        (filter.categoryId  is empty or asset.category_id  == filter.categoryId)    and
        (filter.assetType   is empty or asset.asset_type   == filter.assetType)     and
        (filter.status      is empty or computeLifecycle(asset).status == filter.status) and  // BR-FL-05
        matchesQuery(asset, filter.query or "")                                     // BR-SR-06 AND with search
    )
// Within a single multi-value filter, use OR across that filter's values (BR-FL-03).
// resultCount = applyFilters(...).length  (BR-FL-09 / BR-SR-09)
```

---

## 6. Validation Rules

Light runtime validation in the POC; these become DB constraints / server validation in future phases. Complements logical integrity rules in `docs/06`.

### 6.1 Lifecycle input validation (see §1)

- `construction_year`: present, integer, **≤ currentYear** (BR-LC-06/07).
- `expected_life`: present, integer, **> 0** (BR-LC-06/08).
- Invalid input → **Unknown** status; never crash.

### 6.2 Categorization validation

| ID | Rule |
|---|---|
| BR-CT-01 | Every asset belongs to **exactly one category** and **exactly one asset type** within that category. |
| BR-CT-02 | An asset's `asset_type` must be a **valid sub-type** of its category. |
| BR-CT-03 | Category counts must **reconcile**: sum of per-category counts == total asset count. |
| BR-CT-04 | A category with **zero assets** is still displayed with a count of 0 (not hidden). |

### 6.3 Location validation

| ID | Rule |
|---|---|
| BR-LO-01 | When present, `latitude ∈ [−90, 90]` and `longitude ∈ [−180, 180]`. |
| BR-LO-02 | Missing/invalid coordinates **do not block** display of the rest of the asset record. |
| BR-LO-03 | If coordinates are missing/invalid → map shows **"location unavailable"**; address still shown if present. |
| BR-LO-04 | Address is **optional**; absence handled gracefully. |

### 6.4 Photo validation

| ID | Rule |
|---|---|
| BR-PH-01 | Each photo is associated with **exactly one asset**. |
| BR-PH-02 | Photo display order is **deterministic** (by `sequence`, else load order). |
| BR-PH-03 | A missing/broken image source renders a **placeholder**, not a broken element. |
| BR-PH-04 | An asset with **no photos** shows an **empty state**, not an error. |

### 6.5 Data integrity (runtime)

| ID | Rule |
|---|---|
| BR-DI-01 | `asset_number` is **unique**; duplicates are a data error surfaced during development. |
| BR-DI-02 | Every asset references a **valid panchayat and category**; orphan references are invalid. |
| BR-DI-03 | Every hierarchy node references a **valid parent**. |
| BR-DI-04 | Every photo references a **valid asset**. |
| BR-DI-05 | Counts displayed anywhere **derive from the live dataset** — no hard-coded totals. |

---

## 7. Rule Precedence & Consistency

| ID | Rule |
|---|---|
| BR-PR-01 | This document (and `docs/07`) **overrides any conflicting rule** elsewhere. |
| BR-PR-02 | **Exactly one implementation** of each calculation (lifecycle, status, aggregation), reused across the app. |
| BR-PR-03 | Status thresholds, hierarchy order, and filter semantics are **centralized** (e.g., `domain/constants`) so a future change is one edit. |
| BR-PR-04 | All rules hold **identically** whether data comes from mock JSON or future APIs/database. |

---

## 8. Rule → Consumer Map (Quick Reference)

| Domain | Rule IDs | Primary Consumers |
|---|---|---|
| Lifecycle Calculation | BR-LC-01…09 | `domain/lifecycle`, Dashboard, Lists, Detail, Lifecycle View |
| Asset Status | BR-HL-01…09 | `domain/lifecycle`, `StatusBadge`, Dashboard health summary |
| Navigation | BR-NV-01…10 | Routing, Breadcrumb, drill-down, Asset List |
| Search | BR-SR-01…09 | `FilterBar`, data provider, Asset List |
| Filter | BR-FL-01…10 | `FilterBar`, data provider, Asset List |
| Categorization | BR-CT-01…04 | Category screens, aggregation |
| Location | BR-LO-01…04 | Location View, Asset Detail |
| Photo | BR-PH-01…04 | Photo Gallery, Asset Detail |
| Data Integrity | BR-DI-01…05 | Mock data authoring, all modules |
| Precedence | BR-PR-01…04 | Governance |

---

## 9. Implementation Checklist (Business Logic)

- [ ] `domain/lifecycle` implements BR-LC-* and BR-HL-* **exactly**, including the RL=5 and RL=0 boundaries and Unknown handling.
- [ ] Thresholds defined **once** (`NEAR_EXPIRY_YEARS = 5`); no magic numbers.
- [ ] Status is **computed at runtime** everywhere; **never stored** in JSON, state, or DB.
- [ ] Search trims, lowercases, and substring-matches `asset_name`/`asset_number` (BR-SR-*).
- [ ] Filters combine **AND across filters**, **OR within a multi-value filter** (BR-FL-02/03), with Panchayat constrained by Zone (BR-FL-04).
- [ ] Status filter uses **computed** status (BR-FL-05).
- [ ] Result count tracks combined search + filter state (BR-SR-09 / BR-FL-09).
- [ ] Empty/edge states for no-results, no-photos, no-coordinates, unknown status (BR-NV-08, BR-PH-04, BR-LO-03, BR-HL-04).
- [ ] All counts derive from the live dataset (BR-DI-05).
- [ ] Unit tests cover the lifecycle boundary table and aggregation reconciliation (BR-CT-03, BR-HL-08).

---

*End of BUSINESS_RULES.md*
