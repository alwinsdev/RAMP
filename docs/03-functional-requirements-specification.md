# Functional Requirements Specification (FRS) — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-03 |
| Document Title | Functional Requirements Specification |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Product Owner, Architects, Developers, QA |
| Related Documents | 02-BRD, 04-Screen Flow, 07-Business Rules, 08-Mock Data |

---

## 1. Introduction

This document specifies the functional requirements for each module of the RAMP POC. It is the primary reference for development and testing. Every requirement is written assuming the application consumes data through an abstracted data/service layer that, in Phase 1, is backed by **mock JSON** and, in future phases, by **live APIs and a database** — without changes to the consuming screens.

**Requirement ID convention:** `FR-<MODULE>-<NN>` (e.g., `FR-DASH-01`).

**Modules covered:**
1. Dashboard
2. Asset Category Management
3. Asset Management
4. Asset Location Management
5. Asset Photo Management
6. Asset Lifecycle Monitoring
7. Search & Filter
8. Hierarchy Navigation

**Lifecycle reference (used throughout):**
- `Current Age = Current Year − Construction Year`
- `Remaining Life = Expected Life − Current Age`
- Status: **Healthy** if Remaining Life > 5; **Near Expiry** if 0 < Remaining Life ≤ 5; **Expired** if Remaining Life ≤ 0.

---

## 2. Module 1 — Dashboard

### 2.1 Objective
Provide a single landing view that summarizes the entire asset portfolio with key counts and health indicators, and acts as the entry point for drill-down navigation into the hierarchy.

### 2.2 Features
- Total Asset Count
- Asset Category Count
- Zone-wise Asset Count
- Panchayat-wise Asset Count
- Lifecycle Summary (counts by Healthy / Near Expiry / Expired)
- Asset Health Indicators (visual status distribution)
- Drill-down Navigation entry points

### 2.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-DASH-01 | The system shall display the total count of all assets in the dataset. |
| FR-DASH-02 | The system shall display the total number of distinct asset categories. |
| FR-DASH-03 | The system shall display asset counts grouped by Zone. |
| FR-DASH-04 | The system shall display asset counts grouped by Panchayat. |
| FR-DASH-05 | The system shall display a lifecycle summary showing the number of Healthy, Near Expiry, and Expired assets. |
| FR-DASH-06 | The system shall present health indicators visually (e.g., colored cards/segments) reflecting the status distribution. |
| FR-DASH-07 | The system shall allow the user to drill down from a dashboard metric (e.g., a zone or category) into the corresponding filtered asset list. |
| FR-DASH-08 | All dashboard metrics shall be derived from the current dataset at load time so they always reconcile with underlying data. |
| FR-DASH-09 | The dashboard shall load all required summary data through the data/service layer, never by reading mock files directly. |

### 2.4 User Actions
- View summary metrics on landing.
- Click/tap a zone count to view assets in that zone.
- Click/tap a panchayat count to view assets in that panchayat.
- Click/tap a category to view that category's assets.
- Click/tap a health segment (e.g., "Near Expiry") to view assets with that status.

### 2.5 Business Rules
- BR-DASH-01: Counts must always reflect the live dataset; no hard-coded totals.
- BR-DASH-02: Lifecycle status used in summaries must be computed using the lifecycle formulas, not stored values.
- BR-DASH-03: Drill-down from a metric must carry the corresponding filter context to the destination list.

### 2.6 Validation Rules
- VR-DASH-01: If the dataset is empty, display zeros and an empty-state message rather than errors.
- VR-DASH-02: Percentages (if shown) must sum to 100% (±rounding) across the three statuses.
- VR-DASH-03: Any asset missing construction year or expected life must be excluded from lifecycle computation and reported under an "Unknown/Incomplete" tally rather than miscounted.

### 2.7 Acceptance Criteria
- AC-DASH-01: Given the mock dataset, the total asset count equals the number of asset records.
- AC-DASH-02: Zone-wise and panchayat-wise counts sum to the total asset count.
- AC-DASH-03: Healthy + Near Expiry + Expired (+ Incomplete) equals the total asset count.
- AC-DASH-04: Clicking a zone metric navigates to an asset list filtered to that zone.
- AC-DASH-05: With an empty dataset, the dashboard renders zeros and an empty state without crashing.

---

## 3. Module 2 — Asset Category Management

### 3.1 Objective
Allow users to browse the asset category taxonomy (categories and their sub-types) and use it as a navigation and filtering axis.

### 3.2 Features
- List of asset categories (Educational, Healthcare, Water Infrastructure, Public Infrastructure).
- Display of sub-types within each category.
- Per-category asset count.
- Navigation from a category into its asset list.

### 3.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-CAT-01 | The system shall display all asset categories. |
| FR-CAT-02 | The system shall display the sub-types belonging to each category. |
| FR-CAT-03 | The system shall display the number of assets in each category. |
| FR-CAT-04 | The system shall allow navigation from a category to the list of assets in that category. |
| FR-CAT-05 | Category data shall be retrieved via the data/service layer. |

### 3.4 User Actions
- View all categories and their sub-types.
- Select a category to view its assets.
- Read the asset count per category.

### 3.5 Business Rules
- BR-CAT-01: Every asset must belong to exactly one category and one sub-type within that category.
- BR-CAT-02: Category counts must reconcile with the total asset count when summed.

### 3.6 Validation Rules
- VR-CAT-01: A category with zero assets must still render with a count of 0 (not hidden).
- VR-CAT-02: Sub-types must be displayed under their correct parent category only.

### 3.7 Acceptance Criteria
- AC-CAT-01: All four categories are listed with their sub-types.
- AC-CAT-02: Sum of per-category asset counts equals total assets.
- AC-CAT-03: Selecting a category opens an asset list filtered to that category.

---

## 4. Module 3 — Asset Management

### 4.1 Objective
Provide the ability to view lists of assets and the complete detail record for any single asset, consolidating administrative, location, lifecycle, and media information.

### 4.2 Features
- Asset list (with key columns: number, name, category/type, panchayat, status).
- Asset detail view (full record).
- Status indicator per asset in lists.
- Entry into Location, Photos, and Lifecycle views from the detail.

### 4.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-ASST-01 | The system shall display a list of assets with key identifying and status fields. |
| FR-ASST-02 | The system shall display a full detail view for a selected asset, including Administrative, Asset, Location, Lifecycle, and Media information. |
| FR-ASST-03 | The system shall display the computed lifecycle status for each asset in lists and detail. |
| FR-ASST-04 | The system shall allow navigation from an asset detail to its Photos, Location, and Lifecycle views. |
| FR-ASST-05 | The asset list shall be filterable and searchable (see Module 7). |
| FR-ASST-06 | Asset data shall be retrieved via the data/service layer. |
| FR-ASST-07 | (Future-ready) The asset detail structure shall accommodate create/edit forms in a later phase without redesign. |

### 4.4 User Actions
- Browse the asset list.
- Open an asset to view its detail.
- Navigate to the asset's photos, location, or lifecycle from the detail.
- Filter/search the list (Module 7).

### 4.5 Business Rules
- BR-ASST-01: Each asset has a unique Asset Number.
- BR-ASST-02: Each asset is associated with exactly one State, District, Zone, and Panchayat.
- BR-ASST-03: Each asset references exactly one category and one sub-type/asset type.
- BR-ASST-04: Lifecycle status shown must be computed, not stored.

### 4.6 Validation Rules
- VR-ASST-01: Asset Number must be present and unique.
- VR-ASST-02: Latitude must be within −90 to 90; longitude within −180 to 180 (for location display).
- VR-ASST-03: Construction Year must be ≤ current year; Expected Life must be a positive integer.
- VR-ASST-04: If lifecycle fields are missing/invalid, status displays as "Unknown" and the asset is flagged as incomplete.

### 4.7 Acceptance Criteria
- AC-ASST-01: The asset list displays all assets matching the current filter/search context.
- AC-ASST-02: Selecting an asset opens a detail view showing all five information groups.
- AC-ASST-03: The detail view's status matches the lifecycle rules.
- AC-ASST-04: From the detail, the user can reach the asset's photos, location, and lifecycle views.

---

## 5. Module 4 — Asset Location Management

### 5.1 Objective
Capture and present the geographic location of each asset, including a human-readable address and machine coordinates, with a map or coordinate-based visualization.

### 5.2 Features
- Display of address.
- Display of latitude and longitude.
- Map/coordinate view (pin or coordinate readout) for the asset.
- Location section embedded within the asset detail and available as a focused view.

### 5.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-LOC-01 | The system shall display the asset's full address. |
| FR-LOC-02 | The system shall display the asset's latitude and longitude. |
| FR-LOC-03 | The system shall render a map or coordinate visualization positioning the asset. |
| FR-LOC-04 | The system shall handle assets with missing coordinates gracefully (show address only / "location unavailable"). |
| FR-LOC-05 | Location data shall be retrieved via the data/service layer. |

### 5.4 User Actions
- View an asset's address and coordinates.
- View the asset's position on a map/coordinate display.
- Return from the location view to the asset detail.

### 5.5 Business Rules
- BR-LOC-01: Coordinates, when present, must fall within valid global ranges.
- BR-LOC-02: A missing coordinate pair must not block display of the rest of the asset record.

### 5.6 Validation Rules
- VR-LOC-01: Latitude ∈ [−90, 90]; longitude ∈ [−180, 180].
- VR-LOC-02: If either coordinate is absent or invalid, suppress the map pin and show a clear "location unavailable" indicator.
- VR-LOC-03: Address, if present, must render even when coordinates are absent.

### 5.7 Acceptance Criteria
- AC-LOC-01: An asset with valid coordinates shows a map/coordinate view positioned correctly.
- AC-LOC-02: An asset without coordinates shows its address and a graceful "location unavailable" state.
- AC-LOC-03: Invalid coordinate values do not crash the view.

---

## 6. Module 5 — Asset Photo Management

### 6.1 Objective
Associate photographic evidence with each asset and present it in a gallery, supporting visual verification of the asset's existence and condition.

### 6.2 Features
- Photo gallery for an asset (thumbnails).
- Enlarged/preview view of a selected photo.
- Empty state when an asset has no photos.
- Photo metadata display where available (e.g., caption/label).

### 6.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-PHOTO-01 | The system shall display all photos associated with an asset in a gallery. |
| FR-PHOTO-02 | The system shall allow a photo to be viewed enlarged/previewed. |
| FR-PHOTO-03 | The system shall display an empty state when an asset has no photos. |
| FR-PHOTO-04 | Photo references shall be retrieved via the data/service layer. |
| FR-PHOTO-05 | (Future-ready) The photo model shall support upload and association in a later phase without redesign. |

### 6.4 User Actions
- View the photo gallery for an asset.
- Select a photo to enlarge.
- Close the enlarged view to return to the gallery.

### 6.5 Business Rules
- BR-PHOTO-01: Photos must be linked to exactly one asset.
- BR-PHOTO-02: The gallery order should be deterministic (e.g., by sequence/added order) for the POC.

### 6.6 Validation Rules
- VR-PHOTO-01: A broken/missing image reference must show a placeholder, not a broken element.
- VR-PHOTO-02: Assets with zero photos must show the empty state, never an error.

### 6.7 Acceptance Criteria
- AC-PHOTO-01: An asset with photos shows them all as thumbnails.
- AC-PHOTO-02: Selecting a thumbnail opens an enlarged view.
- AC-PHOTO-03: An asset without photos shows a clear empty state.
- AC-PHOTO-04: A missing image source renders a placeholder.

---

## 7. Module 6 — Asset Lifecycle Monitoring

### 7.1 Objective
Automatically derive and present each asset's age, remaining useful life, and health status, enabling proactive identification of assets approaching or past end-of-life.

### 7.2 Features
- Display of Construction Year and Expected Life.
- Computed Current Age and Remaining Life.
- Computed Health Status (Healthy / Near Expiry / Expired).
- Visual status indicator (color-coded).
- Lifecycle section within asset detail and aggregated into the dashboard summary.

### 7.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-LIFE-01 | The system shall compute Current Age as `Current Year − Construction Year`. |
| FR-LIFE-02 | The system shall compute Remaining Life as `Expected Life − Current Age`. |
| FR-LIFE-03 | The system shall assign status: Healthy (Remaining Life > 5), Near Expiry (0 < Remaining Life ≤ 5), Expired (Remaining Life ≤ 0). |
| FR-LIFE-04 | The system shall present the status with a clear visual indicator. |
| FR-LIFE-05 | The system shall recompute status dynamically based on the current year (never store a fixed status). |
| FR-LIFE-06 | The system shall feed lifecycle status counts into the dashboard summary. |
| FR-LIFE-07 | Lifecycle inputs shall be retrieved via the data/service layer. |

### 7.4 User Actions
- View an asset's lifecycle figures and status.
- Use lifecycle status as a filter axis (via Module 7).
- Navigate from a dashboard health segment into matching assets.

### 7.5 Business Rules
- BR-LIFE-01: Status thresholds are exactly: Healthy > 5y; Near Expiry > 0 and ≤ 5y; Expired ≤ 0y.
- BR-LIFE-02: "Current Year" is the runtime/system year.
- BR-LIFE-03: Status is always derived; it is never persisted as a fixed field.
- BR-LIFE-04: Boundary handling: Remaining Life exactly equal to 5 is **Near Expiry**; exactly 0 is **Expired**.

### 7.6 Validation Rules
- VR-LIFE-01: Construction Year must be a valid year ≤ current year.
- VR-LIFE-02: Expected Life must be a positive integer.
- VR-LIFE-03: If inputs are missing/invalid, status is "Unknown" and the asset is flagged incomplete (excluded from health percentages).
- VR-LIFE-04: Negative Remaining Life is valid and maps to Expired.

### 7.7 Acceptance Criteria
- AC-LIFE-01: For Construction Year 2010, Expected Life 30, current year 2026 → Age 16, Remaining 14 → Healthy.
- AC-LIFE-02: For Construction Year 2000, Expected Life 25, current year 2026 → Age 26, Remaining −1 → Expired.
- AC-LIFE-03: For Remaining Life exactly 5 → Near Expiry; exactly 0 → Expired.
- AC-LIFE-04: An asset with missing Expected Life shows status "Unknown" and is flagged incomplete.

---

## 8. Module 7 — Search & Filter

### 8.1 Objective
Enable users to quickly locate assets through free-text search and to narrow asset lists using structured filters across the hierarchy, category, and lifecycle status.

### 8.2 Features
- Free-text search (by asset name and/or asset number).
- Filter by Zone, Panchayat, Category/Type.
- Filter by Lifecycle Status (Healthy / Near Expiry / Expired).
- Combined filters (AND semantics).
- Clear/reset filters.
- Result count display.

### 8.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-SRCH-01 | The system shall allow free-text search of assets by name and asset number. |
| FR-SRCH-02 | The system shall allow filtering of assets by Zone. |
| FR-SRCH-03 | The system shall allow filtering of assets by Panchayat. |
| FR-SRCH-04 | The system shall allow filtering of assets by Category/Type. |
| FR-SRCH-05 | The system shall allow filtering of assets by Lifecycle Status. |
| FR-SRCH-06 | The system shall combine active filters and search using AND logic. |
| FR-SRCH-07 | The system shall display the number of matching results. |
| FR-SRCH-08 | The system shall allow clearing/resetting all filters and search. |
| FR-SRCH-09 | Search/filter shall operate on data retrieved via the data/service layer (in-memory for the POC; server-side in future). |

### 8.4 User Actions
- Type a query to search assets.
- Apply one or more filters.
- Combine search with filters.
- Reset all filters and search.
- Read the result count.

### 8.5 Business Rules
- BR-SRCH-01: Multiple filters combine with AND; multiple values within a single filter (if supported) combine with OR.
- BR-SRCH-02: Filter options for Panchayat may be constrained by the selected Zone (hierarchy-aware filtering).
- BR-SRCH-03: Lifecycle status filter uses computed status values.

### 8.6 Validation Rules
- VR-SRCH-01: Search must be case-insensitive and trim leading/trailing whitespace.
- VR-SRCH-02: No matches must show a clear "no results" empty state, not an error.
- VR-SRCH-03: Resetting filters must restore the full, unfiltered list.

### 8.7 Acceptance Criteria
- AC-SRCH-01: Searching a known asset name returns that asset.
- AC-SRCH-02: Filtering by a zone returns only assets in that zone.
- AC-SRCH-03: Combining a zone filter and a "Near Expiry" status filter returns only near-expiry assets in that zone.
- AC-SRCH-04: A query with no matches shows the "no results" state.
- AC-SRCH-05: Reset restores the complete list and clears the query.

---

## 9. Module 8 — Hierarchy Navigation

### 9.1 Objective
Provide navigation that mirrors the administrative hierarchy, allowing top-down drill-down (State → District → Zone → Panchayat → Category → Asset) and contextual breadcrumbs back up the chain.

### 9.2 Features
- Drill-down from each hierarchy level to the next.
- Breadcrumb trail reflecting the current position.
- Counts at each level (e.g., assets per zone, per panchayat).
- Entry into the hierarchy from dashboard metrics.

### 9.3 Functional Requirements

| ID | Requirement |
|---|---|
| FR-NAV-01 | The system shall allow navigation from State to its Districts. |
| FR-NAV-02 | The system shall allow navigation from a District to its Zones. |
| FR-NAV-03 | The system shall allow navigation from a Zone to its Panchayats. |
| FR-NAV-04 | The system shall allow navigation from a Panchayat to its Asset Categories. |
| FR-NAV-05 | The system shall allow navigation from a Category to the relevant Asset List. |
| FR-NAV-06 | The system shall display a breadcrumb trail of the current hierarchy path. |
| FR-NAV-07 | The system shall allow navigation back up the hierarchy via breadcrumbs. |
| FR-NAV-08 | The system shall preserve hierarchy context when entering filtered asset lists. |
| FR-NAV-09 | Hierarchy data shall be retrieved via the data/service layer. |

### 9.4 User Actions
- Select a node at any level to drill down.
- Use breadcrumbs to navigate back up.
- Enter the hierarchy from a dashboard metric.
- Reach an asset detail at the end of the chain.

### 9.5 Business Rules
- BR-NAV-01: Each child belongs to exactly one parent (a Panchayat belongs to one Zone, a Zone to one District, etc.).
- BR-NAV-02: Drill-down must carry the accumulated context (selected zone, panchayat, category) into the destination.
- BR-NAV-03: Breadcrumbs must always reflect a valid path from the top of the hierarchy.

### 9.6 Validation Rules
- VR-NAV-01: A level with no children must show an empty state, not a dead end/error.
- VR-NAV-02: Navigating up via a breadcrumb must drop the deeper context appropriately.
- VR-NAV-03: Invalid or unknown node identifiers must redirect to a safe parent level with a message.

### 9.7 Acceptance Criteria
- AC-NAV-01: From a State, the user can reach an individual Asset Detail purely by drilling down.
- AC-NAV-02: The breadcrumb at the asset list reflects State → District → Zone → Panchayat → Category.
- AC-NAV-03: Clicking a breadcrumb segment returns to that level with the correct context.
- AC-NAV-04: A panchayat with no assets in a category shows an empty state.

---

## 10. Cross-Cutting Functional Requirements

| ID | Requirement |
|---|---|
| FR-X-01 | All modules shall access data exclusively through the data/service layer (no direct mock-file access from UI). |
| FR-X-02 | Lifecycle status shall be computed by a single shared function/service reused across dashboard, lists, and detail. |
| FR-X-03 | The data/service layer interface (method signatures/contracts) shall remain stable when the implementation moves from mock JSON to live APIs. |
| FR-X-04 | Empty and error states shall be handled gracefully across all screens. |
| FR-X-05 | Hierarchy context and filters shall be passed via navigation/state so deep links and drill-downs remain consistent. |

---

## 11. Requirements Traceability (Summary)

| Module | Business Goal(s) | Primary Screens (see Doc 04) |
|---|---|---|
| Dashboard | BG-05 | Dashboard |
| Asset Category Management | BG-02 | Category List, Category Detail |
| Asset Management | BG-01 | Asset List, Asset Detail |
| Asset Location Management | BG-03 | Map/Location View |
| Asset Photo Management | BG-07 | Photo Gallery |
| Asset Lifecycle Monitoring | BG-04 | Lifecycle View, Asset Detail, Dashboard |
| Search & Filter | BG-06 | Asset List (with filters) |
| Hierarchy Navigation | BG-02 | All drill-down screens, Breadcrumbs |
