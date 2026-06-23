# Screen Flow Document — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-04 |
| Document Title | Screen Flow Document |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Product Owner, UX, Developers, QA |
| Related Documents | 03-FRS, 05-Wireframes, 11-UI/UX Guidelines, 12-System Flow Diagrams |

---

## 1. Introduction

This document describes every screen in the RAMP POC, the relationships between screens, and the navigation paths a user can take. Each screen is specified with its purpose, components, user actions, navigation flow, and the data it displays. The flows assume all data is supplied by the data/service layer (mock JSON now, APIs later).

The canonical user journey is:

```
Dashboard → Zone → Panchayat → Asset Category → Asset List → Asset Detail → (Photos | Location | Lifecycle)
```

---

## 2. Screen Inventory & Hierarchy

| Screen ID | Screen Name | Level |
|---|---|---|
| SCR-00 | App Shell / Layout (header, breadcrumb, content) | Global |
| SCR-01 | Dashboard | Landing |
| SCR-02 | Zone List | Hierarchy L1 (within selected District/State) |
| SCR-03 | Panchayat List | Hierarchy L2 |
| SCR-04 | Asset Category List | Hierarchy L3 |
| SCR-05 | Asset List | Hierarchy L4 / Results |
| SCR-06 | Asset Detail | Leaf |
| SCR-07 | Photo Gallery | Sub-view of Asset Detail |
| SCR-08 | Map / Location View | Sub-view of Asset Detail |
| SCR-09 | Lifecycle View | Sub-view of Asset Detail |
| SCR-10 | Search & Filter Panel | Overlay/inline on Asset List |
| SCR-11 | Empty / No-Results State | Reusable state |

### 2.1 Screen Hierarchy Tree

```
App Shell (SCR-00)
└── Dashboard (SCR-01)
    ├── [drill: Zone metric]──► Zone List (SCR-02)
    │                            └── Panchayat List (SCR-03)
    │                                 └── Asset Category List (SCR-04)
    │                                      └── Asset List (SCR-05)  ◄── [drill: Category metric from Dashboard]
    │                                           ├── Search & Filter Panel (SCR-10)
    │                                           ├── Empty/No-Results (SCR-11)
    │                                           └── Asset Detail (SCR-06)
    │                                                ├── Photo Gallery (SCR-07)
    │                                                ├── Map / Location View (SCR-08)
    │                                                └── Lifecycle View (SCR-09)
    ├── [drill: Panchayat metric]──► Asset List (SCR-05, filtered by panchayat)
    └── [drill: Health segment]────► Asset List (SCR-05, filtered by status)
```

> Note: The Asset List (SCR-05) is a convergence point — reachable by full drill-down, by dashboard shortcuts (zone/panchayat/category/status), and by search.

---

## 3. Screen Specifications

### SCR-00 — App Shell / Layout (Global)

- **Purpose:** Provide consistent chrome around every screen: header/branding, breadcrumb trail, and the content area. Hosts global navigation context.
- **Components:**
  - Header with application title/logo and (future) user menu.
  - Breadcrumb bar reflecting the current hierarchy path.
  - "Home/Dashboard" action.
  - Content outlet for the active screen.
- **User Actions:**
  - Click app title/Home to return to Dashboard.
  - Click any breadcrumb segment to navigate up the hierarchy.
- **Navigation Flow:** Persistent across all screens; breadcrumb updates as the user drills down or up.
- **Data Displayed:** Current breadcrumb path (State → District → Zone → Panchayat → Category as applicable).

---

### SCR-01 — Dashboard

- **Purpose:** Summarize the asset portfolio and act as the launchpad for drill-down navigation.
- **Components:**
  - Summary cards: Total Assets, Total Categories.
  - Zone-wise count list/cards.
  - Panchayat-wise count list/cards.
  - Lifecycle summary (Healthy / Near Expiry / Expired) with health indicators (color-coded).
  - Drill-down affordances on metrics.
- **User Actions:**
  - View all summary metrics.
  - Click a Zone metric → Zone List or zone-filtered Asset List.
  - Click a Panchayat metric → panchayat-filtered Asset List.
  - Click a Category metric → category-filtered Asset List.
  - Click a Health segment → status-filtered Asset List.
- **Navigation Flow:**
  - → SCR-02 (Zone List) or SCR-05 (filtered Asset List) depending on the metric clicked.
- **Data Displayed:** Total asset count, category count, zone-wise counts, panchayat-wise counts, lifecycle status counts/percentages.

---

### SCR-02 — Zone List

- **Purpose:** Show the zones available (within the current state/district context) with per-zone asset counts, enabling drill-down.
- **Components:**
  - List/grid of zones.
  - Per-zone asset count.
  - Optional per-zone health mini-indicator.
  - Breadcrumb (State → District).
- **User Actions:**
  - Select a zone → Panchayat List.
- **Navigation Flow:**
  - ← From Dashboard (SCR-01).
  - → SCR-03 (Panchayat List) for the selected zone.
- **Data Displayed:** Zone names, asset counts per zone.

---

### SCR-03 — Panchayat List

- **Purpose:** Show panchayats within the selected zone with per-panchayat asset counts.
- **Components:**
  - List/grid of panchayats.
  - Per-panchayat asset count.
  - Breadcrumb (State → District → Zone).
- **User Actions:**
  - Select a panchayat → Asset Category List.
- **Navigation Flow:**
  - ← From Zone List (SCR-02).
  - → SCR-04 (Asset Category List) for the selected panchayat.
- **Data Displayed:** Panchayat names, asset counts per panchayat.

---

### SCR-04 — Asset Category List

- **Purpose:** Within the selected panchayat (or globally, when entered from the Dashboard), display asset categories and sub-types with counts.
- **Components:**
  - Category cards (Educational, Healthcare, Water Infrastructure, Public Infrastructure).
  - Sub-types listed within each category.
  - Per-category asset count.
  - Breadcrumb (… → Panchayat).
- **User Actions:**
  - Select a category → Asset List filtered to that category (and current context).
- **Navigation Flow:**
  - ← From Panchayat List (SCR-03) or Dashboard category metric.
  - → SCR-05 (Asset List) filtered by category + context.
- **Data Displayed:** Category names, sub-types, asset counts per category.

---

### SCR-05 — Asset List

- **Purpose:** Display assets for the current context (hierarchy + category + status filters), with search and filtering, and entry to asset detail.
- **Components:**
  - Result toolbar: result count, search box, filter controls (Zone, Panchayat, Category/Type, Status), reset.
  - Asset table/cards with columns: Asset Number, Asset Name, Category/Type, Panchayat, Status (color-coded).
  - Status badges (Healthy / Near Expiry / Expired / Unknown).
  - Empty/No-Results state (SCR-11) when applicable.
  - Breadcrumb reflecting context.
- **User Actions:**
  - Search by name/number.
  - Apply/clear filters.
  - Sort (optional, by name/number/status).
  - Select an asset → Asset Detail.
- **Navigation Flow:**
  - ← From Category List (SCR-04), Dashboard shortcuts, or search.
  - → SCR-06 (Asset Detail) for the selected asset.
  - ↔ SCR-10 (Search & Filter Panel).
- **Data Displayed:** Filtered list of assets with identifying fields and computed status; result count.

---

### SCR-06 — Asset Detail

- **Purpose:** Present the complete record for one asset, consolidating administrative, asset, location, lifecycle, and media information, with entry points to focused sub-views.
- **Components:**
  - Header: Asset Name, Asset Number, Status badge.
  - Administrative Information panel: State, District, Zone, Panchayat.
  - Asset Information panel: Asset Number, Asset Name, Category, Asset Type.
  - Location summary: Address, Latitude, Longitude (link to Map View).
  - Lifecycle summary: Construction Year, Expected Life, Current Age, Remaining Life, Status (link to Lifecycle View).
  - Photo strip/preview (link to Photo Gallery).
  - Tabs or section links to Photos / Location / Lifecycle.
  - Breadcrumb reflecting full path.
- **User Actions:**
  - View all information groups.
  - Open Photo Gallery (SCR-07).
  - Open Map/Location View (SCR-08).
  - Open Lifecycle View (SCR-09).
  - Navigate back via breadcrumb.
- **Navigation Flow:**
  - ← From Asset List (SCR-05).
  - → SCR-07 / SCR-08 / SCR-09.
- **Data Displayed:** Full asset record (all five information groups), computed lifecycle figures and status.

---

### SCR-07 — Photo Gallery

- **Purpose:** Show all photos for the asset and allow enlarged viewing.
- **Components:**
  - Thumbnail grid.
  - Enlarged/preview overlay.
  - Photo caption/label (where present).
  - Empty state when no photos.
  - Back to Asset Detail.
- **User Actions:**
  - View thumbnails.
  - Select a thumbnail to enlarge.
  - Close enlarged view.
  - Return to Asset Detail.
- **Navigation Flow:**
  - ← From Asset Detail (SCR-06).
- **Data Displayed:** Photo images/references and captions for the asset.

---

### SCR-08 — Map / Location View

- **Purpose:** Visualize the asset's geographic location.
- **Components:**
  - Map/coordinate display with a pin at the asset's coordinates.
  - Address readout.
  - Latitude/longitude readout.
  - "Location unavailable" state when coordinates missing/invalid.
  - Back to Asset Detail.
- **User Actions:**
  - View the asset's location on the map/coordinate display.
  - Read the address and coordinates.
  - Return to Asset Detail.
- **Navigation Flow:**
  - ← From Asset Detail (SCR-06).
- **Data Displayed:** Address, latitude, longitude, map pin.

---

### SCR-09 — Lifecycle View

- **Purpose:** Present the asset's lifecycle figures and health status in focus.
- **Components:**
  - Construction Year, Expected Life.
  - Computed Current Age, Remaining Life.
  - Status badge with explanation of thresholds.
  - Optional visual (timeline/progress bar of life consumed).
  - Back to Asset Detail.
- **User Actions:**
  - View lifecycle figures and status.
  - Return to Asset Detail.
- **Navigation Flow:**
  - ← From Asset Detail (SCR-06).
- **Data Displayed:** Construction year, expected life, computed age, remaining life, computed status.

---

### SCR-10 — Search & Filter Panel

- **Purpose:** Provide structured filtering and free-text search over the Asset List.
- **Components:**
  - Search input (name/number).
  - Filter controls: Zone, Panchayat (constrained by zone), Category/Type, Lifecycle Status.
  - Apply and Reset actions.
  - Active-filter chips and result count.
- **User Actions:**
  - Enter a search query.
  - Select filter values.
  - Apply/reset filters.
- **Navigation Flow:**
  - Inline/overlay on Asset List (SCR-05); updates the list in place.
- **Data Displayed:** Available filter options; current active filters; result count.

---

### SCR-11 — Empty / No-Results State (Reusable)

- **Purpose:** Communicate clearly when a list or view has no data.
- **Components:**
  - Illustrative icon/text.
  - Context message (e.g., "No assets match your filters").
  - Action (e.g., Reset filters / Back).
- **User Actions:**
  - Reset filters or navigate back.
- **Navigation Flow:**
  - Rendered within any list/detail context when no data is available.
- **Data Displayed:** None (state message only).

---

## 4. Primary Navigation Flows

### 4.1 Full Drill-Down (Canonical Journey)
```
Dashboard (SCR-01)
  → Zone List (SCR-02)
    → Panchayat List (SCR-03)
      → Asset Category List (SCR-04)
        → Asset List (SCR-05)
          → Asset Detail (SCR-06)
            → Photo Gallery (SCR-07)
            → Map/Location View (SCR-08)
            → Lifecycle View (SCR-09)
```

### 4.2 Dashboard Shortcuts
```
Dashboard (SCR-01)
  → [Zone metric]      → Asset List filtered by Zone (SCR-05)
  → [Panchayat metric] → Asset List filtered by Panchayat (SCR-05)
  → [Category metric]  → Asset List filtered by Category (SCR-05)
  → [Health segment]   → Asset List filtered by Status (SCR-05)
```

### 4.3 Search-Driven
```
Asset List (SCR-05)
  → Search & Filter Panel (SCR-10)
  → Filtered Asset List (SCR-05) or Empty State (SCR-11)
  → Asset Detail (SCR-06)
```

### 4.4 Upward Navigation
```
Any screen → Breadcrumb segment → corresponding ancestor screen
Asset Detail → Asset List → Category List → Panchayat List → Zone List → Dashboard
```

---

## 5. Navigation Rules Summary

- Every screen (except the Dashboard root) exposes a breadcrumb to navigate upward.
- Drill-down always carries accumulated context (selected zone/panchayat/category/status) to the destination.
- The Asset List is the convergence screen for hierarchy drill-down, dashboard shortcuts, and search.
- Sub-views (Photos/Location/Lifecycle) always return to their parent Asset Detail.
- Empty/error states are shown in place; the user is never left at a dead end.

> Screen layouts are detailed in `05-wireframe-document.md`; visual/UX standards in `11-ui-ux-guidelines.md`; and flows are diagrammed in `12-system-flow-diagrams.md`.
