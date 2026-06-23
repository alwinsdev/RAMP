# Wireframe Document — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-05 |
| Document Title | Wireframe Document (Low-Fidelity) |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Product Owner, UX, Developers |
| Related Documents | 04-Screen Flow, 11-UI/UX Guidelines |

---

## 1. Introduction

This document provides **low-fidelity ASCII wireframes** for the key RAMP POC screens. They communicate layout, hierarchy, and the placement of components — not final visual design. Colors, spacing, and exact styling are governed by `11-ui-ux-guidelines.md`. All data shown is illustrative mock data.

**Screens included:**
1. Dashboard
2. Asset Category Screen
3. Asset List Screen
4. Asset Detail Screen
5. Map / Location View Screen
6. Photo Gallery Screen

**Legend:**
- `[ Button ]` — clickable action/button
- `[▼ Dropdown ]` — select/filter control
- `( ◉ )` / `( ○ )` — selected / unselected option
- `🟢 🟡 🔴` — Healthy / Near Expiry / Expired status indicators
- `▣` — image/photo thumbnail placeholder
- `«Home / … »` — breadcrumb trail

---

## 2. Wireframe — Dashboard (SCR-01)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP — Rural Asset Management Platform                      [ ⌂ Home ]    │
├──────────────────────────────────────────────────────────────────────────┤
│  «Dashboard»                                                               │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌────────────────────┐   ┌────────────────────┐                          │
│  │  TOTAL ASSETS      │   │  ASSET CATEGORIES  │                          │
│  │       128          │   │         4          │                          │
│  └────────────────────┘   └────────────────────┘                          │
│                                                                            │
│  LIFECYCLE / HEALTH SUMMARY                                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐   │
│  │ 🟢 HEALTHY   │  │ 🟡 NEAR      │  │ 🔴 EXPIRED   │  │ ⚪ UNKNOWN    │   │
│  │     86       │  │   EXPIRY 27  │  │     12       │  │      3       │   │
│  │  (click→)    │  │   (click→)   │  │  (click→)    │  │  (click→)    │   │
│  └──────────────┘  └──────────────┘  └──────────────┘  └──────────────┘   │
│  [████████████████████░░░░░░░░░░░░░░░░░░░  health distribution bar  ]      │
│                                                                            │
│  ┌─────────────────────────────┐   ┌─────────────────────────────┐        │
│  │  ZONE-WISE ASSET COUNT      │   │  PANCHAYAT-WISE ASSET COUNT │        │
│  │  North Zone .......... 42 ›│   │  Erumapalayam ........ 18 ›│        │
│  │  South Zone .......... 31 ›│   │  Ammapet ............. 14 ›│        │
│  │  East Zone ........... 29 ›│   │  Kondalampatti ....... 12 ›│        │
│  │  West Zone ........... 26 ›│   │  Jagirammapalayam .... 11 ›│        │
│  │  [ View all zones › ]       │   │  [ View all panchayats › ]  │        │
│  └─────────────────────────────┘   └─────────────────────────────┘        │
│                                                                            │
│  (Click any zone / panchayat / health card to drill into a filtered list) │
└──────────────────────────────────────────────────────────────────────────┘
```

**Notes:** Cards are the primary drill-down affordances. The health distribution bar is a visual restatement of the lifecycle summary.

---

## 3. Wireframe — Asset Category Screen (SCR-04)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP                                                       [ ⌂ Home ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  «Dashboard / Salem / North Zone / Erumapalayam Panchayat»                 │
├──────────────────────────────────────────────────────────────────────────┤
│  ASSET CATEGORIES — Erumapalayam Panchayat                                 │
│                                                                            │
│  ┌───────────────────────────────┐  ┌───────────────────────────────┐     │
│  │  🏫 EDUCATIONAL ASSETS  (7) › │  │  🏥 HEALTHCARE ASSETS   (3) › │     │
│  │   • Primary School            │  │   • Primary Health Centre     │     │
│  │   • Nursery School            │  │   • Rural Health Facility     │     │
│  │   • Government School         │  │                               │     │
│  └───────────────────────────────┘  └───────────────────────────────┘     │
│                                                                            │
│  ┌───────────────────────────────┐  ┌───────────────────────────────┐     │
│  │  💧 WATER INFRASTRUCTURE (5) ›│  │  🏛 PUBLIC INFRASTRUCTURE(4) ›│     │
│  │   • Overhead Water Tank       │  │   • Panchayat Office          │     │
│  │   • Underground Water Tank    │  │   • Community Hall            │     │
│  │   • Bore Well                 │  │                               │     │
│  └───────────────────────────────┘  └───────────────────────────────┘     │
│                                                                            │
│  (Click a category card to view its assets in this panchayat)             │
└──────────────────────────────────────────────────────────────────────────┘
```

**Notes:** Each card shows the category, its sub-types, and the asset count. The count drives drill-down to a filtered Asset List.

---

## 4. Wireframe — Asset List Screen (SCR-05)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP                                                       [ ⌂ Home ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  «Dashboard / Salem / North Zone / Erumapalayam / Educational»            │
├──────────────────────────────────────────────────────────────────────────┤
│  ASSET LIST                                          Results: 7            │
│  ┌──────────────────────────────────────────────────────────────────┐    │
│  │ 🔍 [ Search by name or asset number...            ]  [ Search ]   │    │
│  │ Zone [▼ North] Panchayat [▼ Erumapalayam] Type [▼ All]            │    │
│  │ Status [▼ All]                          [ Apply ] [ Reset ]       │    │
│  │ Active: (Educational ✕)                                           │    │
│  └──────────────────────────────────────────────────────────────────┘    │
│                                                                            │
│  ┌────────────┬──────────────────────────┬───────────────┬───────────┐    │
│  │ ASSET NO.  │ ASSET NAME               │ TYPE          │ STATUS    │    │
│  ├────────────┼──────────────────────────┼───────────────┼───────────┤    │
│  │ EDU-0001   │ Govt Primary School      │ Primary School│ 🟢 Healthy│ ›  │
│  │ EDU-0002   │ Erumapalayam Nursery     │ Nursery School│ 🟡 Near   │ ›  │
│  │ EDU-0003   │ North Govt School        │ Govt School   │ 🔴 Expired│ ›  │
│  │ EDU-0004   │ West Para Primary School │ Primary School│ 🟢 Healthy│ ›  │
│  │ EDU-0005   │ Anganwadi Nursery #2     │ Nursery School│ 🟢 Healthy│ ›  │
│  │ EDU-0006   │ Govt Higher Sec. School  │ Govt School   │ 🟡 Near   │ ›  │
│  │ EDU-0007   │ Colony Primary School    │ Primary School│ ⚪ Unknown│ ›  │
│  └────────────┴──────────────────────────┴───────────────┴───────────┘    │
│                                                                            │
│  (Click a row to open the asset detail)                                   │
└──────────────────────────────────────────────────────────────────────────┘

   EMPTY STATE VARIANT (no matches):
   ┌──────────────────────────────────────────────────────────────────┐
   │                          (  no results  )                         │
   │            No assets match your search and filters.               │
   │                        [ Reset filters ]                          │
   └──────────────────────────────────────────────────────────────────┘
```

**Notes:** Toolbar combines search + filters with AND logic. Status column uses computed lifecycle status with color badges. Table converts to stacked cards on narrow screens (see UI/UX guidelines).

---

## 5. Wireframe — Asset Detail Screen (SCR-06)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP                                                       [ ⌂ Home ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  «… / Erumapalayam / Educational / Govt Primary School»                   │
├──────────────────────────────────────────────────────────────────────────┤
│  GOVT PRIMARY SCHOOL                       Asset No: EDU-0001  🟢 Healthy  │
│  ──────────────────────────────────────────────────────────────────────  │
│  [ Overview ] [ Photos ] [ Location ] [ Lifecycle ]                       │
│                                                                            │
│  ┌─────────────────────────────┐  ┌─────────────────────────────┐         │
│  │ ADMINISTRATIVE INFORMATION  │  │ ASSET INFORMATION           │         │
│  │ State ......... Tamil Nadu  │  │ Asset Number .... EDU-0001  │         │
│  │ District ...... Salem       │  │ Asset Name ...... Govt Pri… │         │
│  │ Zone .......... North Zone  │  │ Category ........ Educational│        │
│  │ Panchayat ..... Erumapalayam│  │ Asset Type ...... Primary Sc│        │
│  └─────────────────────────────┘  └─────────────────────────────┘         │
│                                                                            │
│  ┌─────────────────────────────┐  ┌─────────────────────────────┐         │
│  │ LOCATION         [ Map › ]  │  │ LIFECYCLE     [ Details › ] │         │
│  │ Address: 12 School Rd,      │  │ Construction Year ..... 2010│         │
│  │   Erumapalayam, Salem       │  │ Expected Life ......... 30y │         │
│  │ Lat: 11.6643  Lng: 78.1460  │  │ Current Age ........... 16y │         │
│  │ [ small map preview ▣ ]     │  │ Remaining Life ........ 14y │         │
│  │                             │  │ Status ........ 🟢 Healthy  │         │
│  └─────────────────────────────┘  └─────────────────────────────┘         │
│                                                                            │
│  PHOTOS                                                  [ View gallery › ]│
│  [ ▣ ] [ ▣ ] [ ▣ ] [ +2 more ]                                           │
└──────────────────────────────────────────────────────────────────────────┘
```

**Notes:** Five information groups (Administrative, Asset, Location, Lifecycle, Media) are visible together. Tabs/links open the focused sub-views. Lifecycle figures are computed, not stored.

---

## 6. Wireframe — Map / Location View Screen (SCR-08)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP                                                       [ ⌂ Home ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  «… / Govt Primary School / Location»            [ ‹ Back to detail ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  LOCATION — Govt Primary School                                           │
│  ┌──────────────────────────────────────────────────────────────────┐    │
│  │                                                                    │    │
│  │                              ◉  ← asset pin                        │    │
│  │            (  map / coordinate canvas with pin  )                  │    │
│  │                                                                    │    │
│  │                                                                    │    │
│  └──────────────────────────────────────────────────────────────────┘    │
│  Address    : 12 School Road, Erumapalayam, Salem, Tamil Nadu             │
│  Latitude   : 11.6643                                                      │
│  Longitude  : 78.1460                                                      │
│                                                                            │
│  LOCATION UNAVAILABLE VARIANT:                                            │
│  ┌──────────────────────────────────────────────────────────────────┐    │
│  │   ⚠ Location coordinates unavailable for this asset.              │    │
│  │   Address: (shown if present)                                     │    │
│  └──────────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────────┘
```

**Notes:** Map canvas places a pin at (lat, lng). If coordinates are missing/invalid, the unavailable variant is shown while still rendering the address if present.

---

## 7. Wireframe — Photo Gallery Screen (SCR-07)

```
┌──────────────────────────────────────────────────────────────────────────┐
│  RAMP                                                       [ ⌂ Home ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  «… / Govt Primary School / Photos»              [ ‹ Back to detail ]     │
├──────────────────────────────────────────────────────────────────────────┤
│  PHOTO GALLERY — Govt Primary School (5 photos)                           │
│                                                                            │
│  ┌────────┐  ┌────────┐  ┌────────┐  ┌────────┐                           │
│  │   ▣    │  │   ▣    │  │   ▣    │  │   ▣    │                           │
│  │ Front  │  │ Block A│  │ Ground │  │ Signage│                           │
│  └────────┘  └────────┘  └────────┘  └────────┘                           │
│  ┌────────┐                                                                │
│  │   ▣    │      (click a thumbnail to enlarge)                           │
│  │ Toilet │                                                                │
│  └────────┘                                                                │
│                                                                            │
│  ENLARGED OVERLAY:                                                         │
│  ┌──────────────────────────────────────────────────────────────────┐    │
│  │  [ ✕ Close ]                                                       │    │
│  │  ┌──────────────────────────────────────────────────────────┐     │    │
│  │  │                                                          │     │    │
│  │  │                    ▣  (enlarged photo)                   │     │    │
│  │  │                                                          │     │    │
│  │  └──────────────────────────────────────────────────────────┘     │    │
│  │  Caption: Front view of Govt Primary School                       │    │
│  │  [ ‹ Prev ]                                          [ Next › ]    │    │
│  └──────────────────────────────────────────────────────────────────┘    │
│                                                                            │
│  EMPTY STATE VARIANT:                                                      │
│  ┌──────────────────────────────────────────────────────────────────┐    │
│  │            ( no photos )  No photos available for this asset.      │    │
│  └──────────────────────────────────────────────────────────────────┘    │
└──────────────────────────────────────────────────────────────────────────┘
```

**Notes:** Thumbnails open an enlarged overlay with prev/next and caption. Missing image sources render a placeholder; assets with no photos show the empty state.

---

## 8. Responsive Behavior (Low-Fidelity)

On narrow (tablet/mobile-width) viewports:

```
DESKTOP (multi-column)            NARROW (stacked)
┌────────┐ ┌────────┐             ┌──────────────────┐
│ Card A │ │ Card B │     ──►     │ Card A           │
└────────┘ └────────┘             ├──────────────────┤
┌────────┐ ┌────────┐             │ Card B           │
│ Card C │ │ Card D │             ├──────────────────┤
└────────┘ └────────┘             │ Card C           │
                                  ├──────────────────┤
TABLE ROWS  ──► STACKED CARDS:    │ Card D           │
                                  └──────────────────┘
┌──────────────────────────────┐
│ EDU-0001                     │
│ Govt Primary School          │
│ Primary School   🟢 Healthy ›│
└──────────────────────────────┘
```

> See `11-ui-ux-guidelines.md` for breakpoints, card/table standards, and navigation patterns that these wireframes adhere to.
