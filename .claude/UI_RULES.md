# UI_RULES.md — RAMP

> UI/UX standards Claude Code must follow when building any screen or component. Derived from `docs/11-ui-ux-guidelines.md`. These are build-ready rules; the source doc has the full rationale.

**Design north star:** A district officer should open RAMP and, within seconds, understand *how many assets exist, where they are, and which need attention* — without training.

---

## 1. Canonical Status Colors (Single Source of Truth)

These map directly to the lifecycle statuses (`BUSINESS_RULES.md`). **No screen may invent its own status colors.**

| Status | Label (exact) | Color | Hex | Meaning |
|---|---|---|---|---|
| Healthy | `Healthy` | Green | `#1E8E3E` | Remaining Life > 5 yrs |
| Near Expiry | `Near Expiry` | Amber | `#F9A825` | 0 < Remaining Life ≤ 5 yrs |
| Expired | `Expired` | Red | `#D93025` | Remaining Life ≤ 0 |
| Unknown | `Unknown` | Grey | `#80868B` | Missing/invalid inputs |

**Rules:**

- Color is **always paired with a text label or icon** — never color alone (accessibility).
- Use a **filled pill/badge** in lists and cards; a larger **banner** on the Asset Detail screen.
- The **same hex values** are reused in dashboard charts, list badges, map markers, and detail banners.
- Render status only via the shared `StatusBadge` component, fed by the lifecycle service.

**Neutral / brand tokens** (define once in `styles/` tokens):

| Token | Hex | Use |
|---|---|---|
| `surface` | `#FFFFFF` | Card/panel backgrounds |
| `background` | `#F5F6F8` | App canvas |
| `border` | `#E0E0E0` | Card borders, table dividers |
| `text-primary` | `#202124` | Headings, primary text |
| `text-secondary` | `#5F6368` | Labels, metadata, captions |
| `primary` | `#1A73E8` | Primary actions, links, active nav |
| `primary-hover` | `#1765CC` | Hover/pressed primary |

**Typography & spacing essentials:**

- One sans-serif family; line-height ≥ 1.4; numerics right-aligned/tabular.
- Page title (H1) 24–28px/600 (one per screen); KPI numbers 32–40px/700; body 14–16px.
- Base spacing unit **8px** (use 8/16/24/32); card padding 16px (mobile) → 24px (desktop).
- Page max content width **1200–1280px**, centered.

---

## 2. Layout Standards

Every screen **below the Dashboard** shares the same shell: persistent app header → breadcrumb bar → page content.

```
┌────────────────────────────────────────────────────────────┐
│  [ RAMP Logo ]        Rural Asset Management Platform        │  ← App header (persistent)
├────────────────────────────────────────────────────────────┤
│  Home / Salem / North Zone / Erumapalayam / Primary School   │  ← Breadcrumb bar
├────────────────────────────────────────────────────────────┤
│                     PAGE CONTENT AREA                        │
└────────────────────────────────────────────────────────────┘
```

| ID | Rule |
|---|---|
| LR-01 | The **app header** is persistent across all screens (logo + product name). |
| LR-02 | The **breadcrumb bar** appears on every screen **below** the Dashboard. The Dashboard is Home and shows no breadcrumb. |
| LR-03 | Content sits within the **centered max-width container**; never run text edge-to-edge on wide monitors. |
| LR-04 | **One H1 page title** per screen, at the top of the content area. |
| LR-05 | Group related content into **cards/panels** on the neutral `background`. |
| LR-06 | Maintain consistent vertical rhythm using the **8px** scale. |
| LR-07 | Primary actions (Search, Filter, View on Map) align to a predictable location — **top-right** of the relevant section. |

**Layout patterns by screen type:**

| Screen type | Pattern |
|---|---|
| Dashboard | KPI row → health summary → zone/panchayat breakdown cards → category breakdown → drill-down |
| Hierarchy node (Zone/Panchayat/Category) | Breadcrumb → title → grid of child cards (each shows a count) |
| Asset List | Breadcrumb → title + result count → filter/search bar → table (desktop) / cards (mobile) |
| Asset Detail | Breadcrumb → asset header + status banner → sectioned panels (Info, Location, Lifecycle, Photos) |
| Map View | Breadcrumb → map canvas (primary) → selected-asset info panel |
| Photo Gallery | Breadcrumb → asset name → responsive image grid → lightbox on select |

**Grid:** 12-column desktop grid. Card grids: 4 cols (desktop) → 2 (tablet) → 1 (mobile). KPI row: 4–6 (desktop) → 2 (tablet) → 1–2 (mobile).

---

## 3. Dashboard Standards

The Dashboard is the **landing screen and command center**. It must answer immediately: *How much? Where? What needs attention?*

**Composition (top to bottom):** KPI Summary Row → Asset Health Summary → Zone/Panchayat Breakdown Cards → Category Breakdown → drill-down entry points.

| ID | Rule |
|---|---|
| DB-01 | Show core KPIs as large numeric cards: **Total Assets**, **Asset Categories**, **Zones**, **Panchayats** (sourced via the aggregation service). |
| DB-02 | KPI card anatomy: small uppercase label (top) + large number (center) + optional sublabel (bottom). |
| DB-03 | KPI numbers use the **largest type** on screen (32–40px/700). |
| DB-04 | KPI cards are **clickable** where a meaningful drill-down exists (e.g., Total Assets → full Asset List). |
| DB-05 | Visualize health distribution using the **canonical status colors** (e.g., a horizontal stacked bar or four mini-cards). |
| DB-06 | Always show **count and label** for each status — not just color. |
| DB-07 | Clicking a **status segment** drills into the Asset List filtered to that status. |
| DB-08 | If any assets are **Unknown**, surface them — never hide incomplete data. |
| DB-09 | Present zone-wise, panchayat-wise, and category-wise counts as **lists/small cards** (name + count per row). |
| DB-10 | Each breakdown row is a **drill-down link** into the corresponding filtered Asset List. |
| DB-11 | Sort breakdowns consistently (by count desc or name) and keep the sort stable. |
| DB-12 | Show a clear **empty state** where a node has zero assets ("No assets recorded for this zone yet"). |
| DB-13 | **All numbers are computed** from the live dataset — never hard-coded. |

**Dashboard Do/Don't:** keep it scannable; lead with totals; make every figure a doorway; use status colors only for status. Don't overload with dense charts or present an un-clickable number where a detail view exists.

---

## 4. Card Standards

Cards are the primary container for grouped info (KPIs, hierarchy children, asset summaries).

```
┌─────────────────────────────┐
│ [icon]  TITLE / LABEL        │  ← header (label or category)
│        42                   │  ← primary value (count) OR key fields
│        Assets               │  ← sublabel
│  ● Near Expiry              │  ← optional status pill
└─────────────────────────────┘
```

| ID | Rule |
|---|---|
| CD-01 | Cards use `surface` background, `border`, subtle radius (8px), padding 16–24px. |
| CD-02 | At most **one primary value** emphasized per card; supporting fields are secondary. |
| CD-03 | **Clickable cards** (hierarchy children, KPIs) show a hover lift/highlight and a clear affordance (cursor/shadow). |
| CD-04 | **Asset summary cards** (mobile list view) show: asset name, asset number, category, panchayat, and a **status pill**. |
| CD-05 | Status pills inside cards use the **canonical color + label**. |
| CD-06 | Category cards carry the category's **icon** for fast recognition. |
| CD-07 | Card grids reflow 4 → 2 → 1 columns (see Responsive). |
| CD-08 | Never overcrowd a card; beyond ~5 fields, move detail behind a "View" action into the detail screen. |

---

## 5. Table Standards

Tables are the **default Asset List presentation on desktop**.

**Standard Asset List columns:**

| Column | Alignment | Notes |
|---|---|---|
| Asset Number | Left | Tabular/monospace; e.g., `EDU-0001` |
| Asset Name | Left | Primary identifier; links to Asset Detail |
| Category | Left | With category icon |
| Asset Type | Left | Sub-type (e.g., Primary School) |
| Panchayat | Left | Location context |
| Status | Center | **Canonical status pill** (color + label) |
| Remaining Life | Right | Numeric (years); tabular alignment |

| ID | Rule |
|---|---|
| TD-01 | Header row is visually distinct (heavier weight, `border` underline) and stays readable. |
| TD-02 | **Numeric columns right-aligned**; text left-aligned; status centered. |
| TD-03 | Use **zebra striping or row dividers**; keep contrast subtle. |
| TD-04 | **Row hover** highlights the full row; the row (or asset name) opens Asset Detail. |
| TD-05 | Status renders as the **canonical pill** — never plain colored text without a label. |
| TD-06 | Show the **result count** above the table ("Showing 8 assets"). |
| TD-07 | When filters/search are active, show **active filters as removable chips** above the table. |
| TD-08 | **Empty state:** no rows → friendly message + a way to clear filters (never an empty grid). |
| TD-09 | If sorting is implemented, indicate it in the active column header; default sort is stable. |
| TD-10 | Avoid horizontal scroll on desktop; drop low-priority columns before scrolling (and use cards at narrow widths). |

**Responsive column-drop order** (lowest priority first, before switching to cards): Asset Type → Remaining Life (status pill already conveys urgency) → Panchayat → Category. **Always keep:** Asset Number, Asset Name, Status.

---

## 6. Navigation Standards

RAMP uses **hierarchical drill-down** with **breadcrumbs** as the primary wayfinding mechanism.

Canonical path: `Dashboard → Zone → Panchayat → Asset Category → Asset List → Asset Detail → (Photos | Location | Lifecycle)`.

| ID | Rule |
|---|---|
| NR-01 | Every aggregate count on the Dashboard is **clickable** and drills into the corresponding filtered view. |
| NR-02 | Navigation is **always reversible** via breadcrumbs — no reliance on browser back. |
| NR-03 | The **Asset List** is the convergence point; all paths lead here and it renders consistently. |
| NR-04 | Selecting an asset opens **Asset Detail**; Photos, Location, Lifecycle are **sub-views** of the asset, not top-level destinations. |
| NR-05 | **Search and Filter** are available wherever a list of assets is shown; results render in the standard Asset List pattern. |
| NR-06 | **No dead-ends**: every screen offers a forward action, a breadcrumb, or both. |

**Breadcrumb standards:**

```
Home / Salem / North Zone / Erumapalayam Panchayat / Primary School / Govt Primary School
```

| ID | Rule |
|---|---|
| BC-01 | First crumb is always **Home** (links to Dashboard). |
| BC-02 | Each crumb reflects a **real node** the user passed through. **District is the top level** (there is no State); the first crumb is Home, the second is the District. |
| BC-03 | Every crumb **except the last** is a link; the last (current screen) is plain text. |
| BC-04 | Consistent delimiter (`/` or chevron) with adequate spacing. |
| BC-05 | On narrow screens, collapse the middle to `Home / … / Current`, keeping first and last visible (tapping `…` may reveal the full trail). |
| BC-06 | Breadcrumb labels match entity names exactly (no meaning-losing abbreviation). |

**Feedback states:** current location reinforced by the H1 + bold final breadcrumb + active nav highlight; all interactive elements show hover and pressed states; data loads show a **loading indicator** (skeleton/spinner) — never a frozen blank screen.

---

## 7. Responsive Design Rules

RAMP must be fully usable from a phone in the field. Layouts **reflow fluidly**; nothing is desktop-only.

**Breakpoints:**

| Token | Range | Layout intent |
|---|---|---|
| `mobile` | < 600px | Single column; tables → cards; collapsed breadcrumbs |
| `tablet` | 600–1023px | Two-column card grids; condensed tables |
| `desktop` | ≥ 1024px | Full multi-column layouts and full tables |

| ID | Rule |
|---|---|
| MR-01 | **Tables → cards:** below `tablet`, the Asset List table converts to a vertical list of **asset summary cards** (name, number, category, panchayat, status pill). |
| MR-02 | **Card grids reflow:** 4 (desktop) → 2 (tablet) → 1 (mobile). |
| MR-03 | **KPI row reflows:** 4–6 (desktop) → 2 (tablet) → 1–2 (mobile), stacked. |
| MR-04 | **Breadcrumbs collapse:** long trails become `Home / … / Current`, preserving first and last. |
| MR-05 | **Touch targets ≥ 44 × 44px**; spacing between tappable rows prevents mis-taps. |
| MR-06 | **Map view:** map fills viewport width; selected-asset info becomes a bottom sheet / stacked panel. |
| MR-07 | **Photo gallery:** grid columns scale (4 → 2 → 1–2); tapping opens a full-screen lightbox with swipe. |
| MR-08 | **No horizontal page scrolling** on mobile (except intentionally scrollable elements like a wide map). |
| MR-09 | Filters/search **stack vertically** and may collapse behind a "Filters" toggle on small screens. |
| MR-10 | Keep type legible (body ≥ 14px); never shrink below readable thresholds to force a desktop layout. |

**Content priority on small screens:** (1) what the asset is (name + number) → (2) its status → (3) where it is (panchayat) → (4) supporting detail (type, category, remaining life).

---

## 8. Accessibility Rules

| ID | Rule |
|---|---|
| AX-01 | Color is **never the sole carrier of meaning** — status always pairs color with a text label/icon. |
| AX-02 | Text contrast meets a readable baseline against its background (aim for **WCAG AA**). |
| AX-03 | All interactive elements are **keyboard-reachable** with a **visible focus** state. |
| AX-04 | Touch targets **≥ 44 × 44px**. |
| AX-05 | Asset photos have **descriptive alt text** (use the photo caption). |
| AX-06 | Headings follow a logical order (**one H1** per screen; nested H2/H3). |
| AX-07 | Breadcrumbs and links have **meaningful, descriptive labels**. |

---

## 9. Interaction & Feedback States (Mandatory)

| State | Requirement |
|---|---|
| **Loading** | Show skeletons or a spinner while data resolves through the data service. Never a frozen blank screen. (This also absorbs future API latency.) |
| **Empty** | Every list, grid, card group, and map handles "no data" with a clear, friendly message and (where relevant) a next action. |
| **Error** | If the data service fails, show a non-technical message ("Couldn't load assets. Please retry.") with a retry affordance. |
| **Hover / Pressed** | All interactive elements show visible hover and pressed states. |
| **Selected** | The active hierarchy location and any selected map marker/list row are visually highlighted. |
| **Focus** | Keyboard focus is always visible (outline). |

> These states are **mandatory** because they make the future swap from mock data to live APIs invisible to the user — latency, emptiness, and failures are already handled.

---

## 10. Per-Screen Consistency Checklist (Use Before Shipping)

- [ ] Status uses the **canonical colors + labels** — no custom status colors.
- [ ] Breadcrumb present (all non-Dashboard screens) and correctly trimmed on mobile.
- [ ] One H1 page title; headings nested logically.
- [ ] Cards/tables follow the standard anatomy and column rules.
- [ ] Every aggregate is a **drill-down doorway**.
- [ ] Table converts to cards below the `tablet` breakpoint.
- [ ] Loading, empty, and error states implemented.
- [ ] Touch targets ≥ 44px; focus states visible.
- [ ] Spacing uses the 8px scale; content within the max-width container.
- [ ] No dead-end screens.

---

*End of UI_RULES.md*
