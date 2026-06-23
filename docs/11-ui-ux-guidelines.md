# UI/UX Guidelines — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-11 |
| Document Title | UI/UX Guidelines |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Developers, Designers, Future Claude (AI) sessions |
| Related Documents | 04-Screen Flow, 05-Wireframes, 07-Business Rules, 09-Roadmap, 10-Claude Development Guide |

---

## 1. Purpose

This document defines the **visual and interaction standards** for the RAMP POC. It exists so that every screen — built in any session, by any developer — looks and behaves consistently. It encodes layout rules, navigation patterns, dashboard principles, card and table design, and responsive behavior.

These guidelines are intentionally **lightweight and POC-appropriate**. They favor clarity, consistency, and speed of implementation over pixel-perfect polish. They are also **future-ready**: nothing here assumes mock data, so the same UI rules hold once the application consumes live APIs.

> **Design north star:** A district officer should be able to open RAMP and, within seconds, understand *how many assets exist, where they are, and which ones need attention* — without training.

---

## 2. Design Principles

| ID | Principle | What it means in practice |
|---|---|---|
| DP-01 | **Clarity over decoration** | Every element earns its place. No ornamental UI. Data is the hero. |
| DP-02 | **Consistency over novelty** | The same action looks the same everywhere. Reuse patterns; don't reinvent per screen. |
| DP-03 | **Hierarchy made visible** | The State → District → Zone → Panchayat → Category → Asset structure should always be apparent through breadcrumbs and headings. |
| DP-04 | **Status at a glance** | Asset health (Healthy / Near Expiry / Expired / Unknown) is communicated instantly through consistent color + label. |
| DP-05 | **Drill-down, never dead-end** | Every aggregate number is a doorway. Users move from summary to detail and back without losing their place. |
| DP-06 | **Forgiving and empty-state aware** | Every list, card, and map handles "no data" gracefully with a clear message, never a blank void. |
| DP-07 | **Accessible by default** | Color is never the *only* signal; sufficient contrast; readable type; keyboard-navigable. |
| DP-08 | **Responsive, not adaptive-only** | Layouts reflow fluidly from desktop to mobile widths (see §9). |

---

## 3. Visual Language

### 3.1 Status Color System (Single Source of Truth)

This palette is **canonical** and must match the lifecycle statuses defined in `07-business-rules.md`. No screen may invent its own status colors.

| Status | Label | Color (name) | Hex | Swatch use |
|---|---|---|---|---|
| Healthy | `Healthy` | Green | `#1E8E3E` | Remaining Life > 5 yrs |
| Near Expiry | `Near Expiry` | Amber | `#F9A825` | 0 < Remaining Life ≤ 5 yrs |
| Expired | `Expired` | Red | `#D93025` | Remaining Life ≤ 0 |
| Unknown | `Unknown` | Grey | `#80868B` | Missing/invalid inputs |

**Mandatory rules**

- Color is **always paired with a text label or icon** — never color alone (accessibility, DP-07).
- Use a **filled pill/badge** for status in lists and cards; a larger banner on the Asset Detail screen.
- The same hex values are reused in dashboard charts, list badges, map markers, and detail banners.

### 3.2 Neutral & Brand Palette

| Token | Hex | Use |
|---|---|---|
| `surface` | `#FFFFFF` | Card and panel backgrounds |
| `background` | `#F5F6F8` | App canvas behind cards |
| `border` | `#E0E0E0` | Card borders, table dividers |
| `text-primary` | `#202124` | Headings, primary text |
| `text-secondary` | `#5F6368` | Labels, metadata, captions |
| `primary` | `#1A73E8` | Primary actions, links, active nav |
| `primary-hover` | `#1765CC` | Hover/pressed state of primary |

> Brand color is deliberately a single accessible blue. The POC does not need a multi-color brand system.

### 3.3 Typography

| Role | Size (desktop) | Weight | Notes |
|---|---|---|---|
| Page title (H1) | 24–28px | 600 | One per screen |
| Section heading (H2) | 20px | 600 | Card/section titles |
| Subheading (H3) | 16px | 600 | Sub-sections, group labels |
| Body | 14–16px | 400 | Default reading size |
| Label / caption | 12–13px | 500 | Field labels, metadata, badges |
| Numeric KPI | 32–40px | 700 | Dashboard summary counts |

- Use **one sans-serif family** (system font stack is acceptable for the POC).
- Line height ≥ 1.4 for body text.
- Numbers in KPIs and tables use **tabular alignment** (right-aligned numerics).

### 3.4 Spacing & Sizing

- Base spacing unit: **8px**. Use multiples (8 / 16 / 24 / 32).
- Card internal padding: **16px** (mobile) to **24px** (desktop).
- Minimum touch target: **44 × 44px**.
- Page max content width: **1200–1280px**, centered, with fluid gutters.

### 3.5 Iconography

- Use a single consistent icon set (outline style).
- Reserve icons for: navigation (breadcrumb chevrons), status reinforcement, category identification, and actions (search, filter, map pin, photo).
- Each **asset category** may carry a recognizable icon (school, health, water, public building) — reused everywhere that category appears.

---

## 4. Layout Rules

### 4.1 Global Application Shell

Every screen below the Dashboard shares the same shell:

```
┌────────────────────────────────────────────────────────────┐
│  [ RAMP Logo ]        Rural Asset Management Platform        │  ← App header (fixed)
├────────────────────────────────────────────────────────────┤
│  Home / Salem / North Zone / Erumapalayam / Primary School   │  ← Breadcrumb bar
├────────────────────────────────────────────────────────────┤
│                                                              │
│                     PAGE CONTENT AREA                        │
│                                                              │
└────────────────────────────────────────────────────────────┘
```

**Rules**

| ID | Rule |
|---|---|
| LR-01 | The **app header** is persistent across all screens (logo + product name). |
| LR-02 | The **breadcrumb bar** appears on every screen **below** the Dashboard (see §5.2). The Dashboard itself shows no breadcrumb (it is Home). |
| LR-03 | Content sits within the centered max-width container; never let text run edge-to-edge on wide monitors. |
| LR-04 | One **H1 page title** per screen, top of content area. |
| LR-05 | Group related content into **cards/panels** on a neutral background for visual separation. |
| LR-06 | Maintain consistent vertical rhythm using the 8px spacing scale. |
| LR-07 | Primary actions (e.g., **Search**, **Filter**, **View on Map**) align to a predictable location — top-right of the relevant section. |

### 4.2 Layout Patterns by Screen Type

| Screen type | Layout pattern |
|---|---|
| Dashboard | KPI summary row → category/zone breakdown cards → health summary → drill-down lists |
| Hierarchy node (Zone, Panchayat, Category) | Breadcrumb → title → grid of child cards (each child = a card with count) |
| Asset List | Breadcrumb → title + result count → filter/search bar → responsive table (desktop) / card list (mobile) |
| Asset Detail | Breadcrumb → asset header + status banner → tabbed/sectioned panels (Info, Location, Lifecycle, Photos) |
| Map View | Breadcrumb → map canvas (primary) → selected-asset info panel |
| Photo Gallery | Breadcrumb → asset name → responsive image grid → lightbox on select |

### 4.3 Grid System

- Use a **12-column responsive grid** on desktop.
- Card grids: 4 columns (desktop) → 2 columns (tablet) → 1 column (mobile).
- KPI summary: 4–6 KPIs per row (desktop) → 2 per row (tablet) → 1–2 (mobile).

---

## 5. Navigation Rules

### 5.1 Navigation Model

RAMP uses **hierarchical drill-down navigation** mirroring the administrative tree. The canonical path is:

```
Dashboard → Zone → Panchayat → Asset Category → Asset List → Asset Detail → (Photos | Location | Lifecycle)
```

| ID | Rule |
|---|---|
| NR-01 | Every aggregate count on the Dashboard is **clickable** and drills into the corresponding filtered view (DP-05). |
| NR-02 | Navigation is **always reversible** — users can step back up the hierarchy via breadcrumbs without browser back. |
| NR-03 | The **Asset List** is the convergence point: multiple paths (by zone, by panchayat, by category, by search) all lead here. |
| NR-04 | Selecting an asset opens **Asset Detail**; Photos, Location, and Lifecycle are sub-views of the asset, not separate top-level destinations. |
| NR-05 | **Search and Filter** are available wherever a list of assets is shown; results render in the standard Asset List pattern. |
| NR-06 | No screen is a dead-end: every screen offers a forward action, a breadcrumb, or both. |

### 5.2 Breadcrumbs

Breadcrumbs are the **primary wayfinding mechanism** and are mandatory on all non-Dashboard screens.

**Format**

```
Home / Salem / North Zone / Erumapalayam Panchayat / Primary School / Govt Primary School
```

**Rules**

| ID | Rule |
|---|---|
| BC-01 | The first crumb is always **Home** (links to Dashboard). |
| BC-02 | Each crumb reflects a **real node** in the hierarchy the user passed through (State is implicit/optional in the POC; District is the first selectable level shown). |
| BC-03 | Every crumb **except the last** is a link; the last (current screen) is plain text. |
| BC-04 | Crumbs are separated by a consistent delimiter (`/` or chevron) with adequate spacing. |
| BC-05 | On narrow screens, collapse the middle of long trails to `Home / … / Current` while keeping first and last visible (see §9). |
| BC-06 | Breadcrumb labels match the entity names exactly (no abbreviation that loses meaning). |

### 5.3 Drill-Down & Back Behavior

- Clicking a **child card** (e.g., a zone) navigates **down** one level.
- Clicking a **breadcrumb crumb** navigates **up** to that level.
- The application preserves the user's path context so back-navigation returns to the same scroll/selection state where feasible.
- Drill-down from a **Dashboard chart segment** (e.g., a category bar) applies that segment as a filter on the Asset List.

### 5.4 Active State & Feedback

- The current location is reinforced by: the page H1, the bold final breadcrumb, and any active nav highlight.
- Interactive elements show **hover** and **pressed/active** states.
- Navigation that loads data shows a **loading indicator** (skeleton or spinner) — never a frozen blank screen.

---

## 6. Dashboard Design Principles

The Dashboard is the **landing screen and command center**. It must answer three questions immediately: *How much? Where? What needs attention?*

### 6.1 Composition (top to bottom)

1. **KPI Summary Row** — headline counts.
2. **Asset Health Summary** — distribution across Healthy / Near Expiry / Expired / Unknown.
3. **Breakdown Cards** — Zone-wise and Panchayat-wise counts.
4. **Category Breakdown** — assets per category.
5. **Drill-down entry points** — every number links deeper.

### 6.2 KPI Cards

| ID | Rule |
|---|---|
| DB-01 | Show core KPIs as large numeric cards: **Total Assets**, **Asset Categories**, **Zones**, **Panchayats** (counts sourced via the aggregation service, see Doc 10). |
| DB-02 | Each KPI card: small uppercase label (top) + large number (center) + optional sublabel/trend (bottom). |
| DB-03 | KPI numbers use the largest type on the screen (32–40px, weight 700). |
| DB-04 | KPI cards are clickable where a meaningful drill-down exists (e.g., Total Assets → full Asset List). |

### 6.3 Health Summary

| ID | Rule |
|---|---|
| DB-05 | Visualize the health distribution using the **canonical status colors** (§3.1) — e.g., a horizontal stacked bar or four mini-cards. |
| DB-06 | Always show the **count and the label** for each status, not just color. |
| DB-07 | Clicking a status segment drills into the Asset List filtered to that status. |
| DB-08 | If any assets are **Unknown**, surface them — do not hide incomplete data. |

### 6.4 Breakdown Cards (Zone / Panchayat / Category)

| ID | Rule |
|---|---|
| DB-09 | Present zone-wise, panchayat-wise, and category-wise counts as **lists or small cards**, each row showing name + count. |
| DB-10 | Each row is a **drill-down link** into the corresponding filtered Asset List. |
| DB-11 | Sort breakdowns sensibly (e.g., by count descending or by name) and keep the sort consistent. |
| DB-12 | Show a clear **empty state** ("No assets recorded for this zone yet") where a node has zero assets. |

### 6.5 Dashboard Do / Don't

**Do** — keep it scannable; lead with totals; make every figure a doorway; use status colors consistently.
**Don't** — overload with dense charts; use status colors for non-status meaning; present a number the user can't click through when a detail view exists.

---

## 7. Card Design

Cards are the primary container for grouped information (KPIs, hierarchy children, asset summaries).

### 7.1 Anatomy

```
┌─────────────────────────────┐
│ [icon]  TITLE / LABEL        │  ← header (label or category)
│                             │
│        42                   │  ← primary value (count) OR key fields
│        Assets               │  ← sublabel
│                             │
│  ● Near Expiry              │  ← optional status pill
└─────────────────────────────┘
```

### 7.2 Rules

| ID | Rule |
|---|---|
| CD-01 | Cards use `surface` background, `border`, subtle radius (8px), and consistent padding (16–24px). |
| CD-02 | A card has at most **one primary value** emphasized; supporting fields are secondary. |
| CD-03 | **Clickable cards** (hierarchy children, KPIs) show a hover lift/highlight and a clear affordance (cursor, subtle shadow). |
| CD-04 | **Asset summary cards** (used in mobile list view) show: asset name, asset number, category, panchayat, and a **status pill**. |
| CD-05 | Status pills inside cards use the canonical color + label (§3.1). |
| CD-06 | Category cards carry the category's icon for fast recognition (DP-04). |
| CD-07 | Card grids reflow per the responsive rules (§9): 4 → 2 → 1 columns. |
| CD-08 | Never overcrowd a card; if content exceeds ~5 fields, move detail behind a "View" action into the detail screen. |

---

## 8. Table Design

Tables are the **default Asset List presentation on desktop**. They prioritize scannability and alignment.

### 8.1 Standard Asset List Columns

| Column | Alignment | Notes |
|---|---|---|
| Asset Number | Left | Monospace/tabular; e.g., `EDU-0001` |
| Asset Name | Left | Primary identifier; links to Asset Detail |
| Category | Left | With category icon |
| Asset Type | Left | Sub-type (e.g., Primary School) |
| Panchayat | Left | Location context |
| Status | Center | **Canonical status pill** (color + label) |
| Remaining Life | Right | Numeric (years); tabular alignment |

### 8.2 Rules

| ID | Rule |
|---|---|
| TD-01 | Table header row is visually distinct (slightly heavier weight, `border` underline) and remains readable. |
| TD-02 | **Numeric columns are right-aligned**; text columns left-aligned; status centered. |
| TD-03 | Use **zebra striping or row dividers** for readability; keep contrast subtle. |
| TD-04 | **Row hover** highlights the full row; the row (or asset name) is the click target to open Asset Detail. |
| TD-05 | Status renders as the canonical pill (§3.1) — never plain colored text without a label. |
| TD-06 | Show the **result count** above the table ("Showing 8 assets"). |
| TD-07 | When filters/search are active, show the **active filters** as removable chips above the table. |
| TD-08 | **Empty state:** when no rows match, show a friendly message and a way to clear filters — never an empty grid. |
| TD-09 | Sorting (if implemented in the POC) is indicated in the active column header; default sort is stable and documented. |
| TD-10 | Avoid horizontal scrolling on desktop; if columns overflow, drop lower-priority columns before scrolling (and rely on the card view at narrow widths). |

### 8.3 Column Priority (for responsive collapse)

When width shrinks, hide columns in this order (lowest priority first), before switching to cards:

1. Asset Type
2. Remaining Life (the **status pill already conveys urgency**)
3. Panchayat
4. Category

**Always keep:** Asset Number, Asset Name, Status.

---

## 9. Mobile Responsiveness

RAMP must remain fully usable from a phone in the field. Layouts **reflow fluidly**; nothing is desktop-only.

### 9.1 Breakpoints

| Token | Range | Primary layout intent |
|---|---|---|
| `mobile` | < 600px | Single column; tables become cards; collapsed breadcrumbs |
| `tablet` | 600–1023px | Two-column card grids; condensed tables |
| `desktop` | ≥ 1024px | Full multi-column layouts and full tables |

### 9.2 Responsive Rules

| ID | Rule |
|---|---|
| MR-01 | **Tables → cards:** below `tablet`, the Asset List table converts to a vertical list of **asset summary cards** (§7.2, CD-04). Each card shows asset name, number, category, panchayat, and status pill. |
| MR-02 | **Card grids reflow:** 4 columns (desktop) → 2 (tablet) → 1 (mobile). |
| MR-03 | **KPI row reflows:** 4–6 across (desktop) → 2 across (tablet) → 1–2 (mobile), stacked. |
| MR-04 | **Breadcrumbs collapse:** long trails become `Home / … / Current`, preserving first and last (BC-05). Tapping `…` can reveal the full trail. |
| MR-05 | **Touch targets** are ≥ 44 × 44px; spacing between tappable rows prevents mis-taps. |
| MR-06 | **Map view:** map fills the viewport width; the selected-asset info panel becomes a bottom sheet / stacked panel rather than a side panel. |
| MR-07 | **Photo gallery:** grid columns scale down (e.g., 4 → 2 → 1–2); tapping opens a full-screen lightbox with swipe. |
| MR-08 | **No horizontal scrolling** of the page on mobile (except intentionally scrollable elements like a wide map). |
| MR-09 | Filters/search controls **stack vertically** and may collapse behind a "Filters" toggle on small screens to save space. |
| MR-10 | Font sizes remain legible (body ≥ 14px); never shrink type below readable thresholds to force a desktop layout. |

### 9.3 Content Priority on Small Screens

When space is constrained, preserve in this order:
1. **What the asset is** (name + number)
2. **Its status** (health)
3. **Where it is** (panchayat)
4. Supporting detail (type, category, remaining life)

---

## 10. Interaction & Feedback States

| State | Requirement |
|---|---|
| **Loading** | Show skeletons or a spinner while data resolves through the data service. Never a frozen blank screen. (Future-ready: the same states cover real API latency.) |
| **Empty** | Every list, grid, card group, and map handles "no data" with a clear, friendly message and (where relevant) a next action. |
| **Error** | If the data service fails, show a non-technical message ("Couldn't load assets. Please retry.") with a retry affordance. |
| **Hover / Pressed** | All interactive elements (cards, rows, links, buttons, breadcrumb links) have visible hover and pressed states. |
| **Selected** | The active hierarchy location and any selected map marker/list row are visually highlighted. |
| **Focus** | Keyboard focus is always visible (outline) for accessibility (DP-07). |

> These states are **mandatory** because they make the swap from mock data to live APIs invisible to the user — latency, emptiness, and failures are already handled.

---

## 11. Accessibility Checklist (POC Baseline)

| ID | Requirement |
|---|---|
| AX-01 | Color is never the sole carrier of meaning — status always pairs color with a text label/icon. |
| AX-02 | Text contrast meets a readable baseline against its background (aim for WCAG AA). |
| AX-03 | All interactive elements are keyboard-reachable with a visible focus state. |
| AX-04 | Touch targets ≥ 44 × 44px. |
| AX-05 | Images (asset photos) have descriptive alt text (use the photo caption). |
| AX-06 | Headings follow a logical order (one H1 per screen, nested H2/H3). |
| AX-07 | Breadcrumbs and links have meaningful, descriptive labels. |

---

## 12. Consistency Checklist (Use Before Shipping a Screen)

- [ ] Status uses the **canonical colors + labels** (§3.1) — no custom status colors.
- [ ] Breadcrumb present (all non-Dashboard screens) and correctly trimmed on mobile.
- [ ] One H1 page title; headings nested logically.
- [ ] Cards/tables follow the standard anatomy and column rules.
- [ ] Every aggregate is a **drill-down doorway** (DP-05).
- [ ] Table converts to cards below the `tablet` breakpoint.
- [ ] Loading, empty, and error states are implemented.
- [ ] Touch targets ≥ 44px; focus states visible.
- [ ] Spacing uses the 8px scale; content within max-width container.
- [ ] No dead-end screens.

---

## 13. Future-Readiness Notes

These guidelines deliberately contain **no mock-data assumptions**. When RAMP migrates from mock JSON to live APIs (see `09-development-roadmap.md` and `10-claude-development-guide.md`):

- **Loading/empty/error states** already absorb real network latency and failures — no redesign needed.
- **Status rendering** stays identical because lifecycle status is computed centrally (Doc 07), not tied to the data source.
- **Responsive patterns** are layout concerns, independent of where data originates.
- **Drill-down navigation** maps cleanly onto API-backed filtered queries (e.g., `assets?zoneId=…&status=…`).

The UI should never need to change because the data source changed — that separation is the core architectural promise of the POC.

---

*End of Document — RAMP-DOC-11*
