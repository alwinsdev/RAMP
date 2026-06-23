# Demo Script — RAMP POC

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-13 |
| Document Title | POC Demo Script |
| Status | POC complete (Phase 1) |
| Stack | Laravel 12 · Livewire 3 · Alpine · Tailwind v4 · ApexCharts · Google Maps |
| Audience | Sponsors, Product Owner, Stakeholders |

> A 5–7 minute walkthrough proving the POC's value: a single, trusted window into rural public assets, with health visible at a glance and a future-ready architecture.

---

## 1. Setup (before the demo)

```bash
composer install
npm install
cp .env.example .env        # if not already present
php artisan key:generate
npm run build               # or: npm run dev  (Vite HMR)
php artisan serve           # http://127.0.0.1:8000
```

- **No database required** — Phase 1 runs entirely on mock JSON (`storage/app/mock-data/*.json`): **100 assets across 13 panchayats in Salem & Erode districts**, with real coordinates and real category images (`public/asset-images/`).
- Set `GOOGLE_MAPS_API_KEY` in `.env` for the live map on the Location view (already configured in this build). Without it, the Location view shows a graceful coordinate preview.
- Verify everything is green: `php artisan test` → **73 passing**.

---

## 2. The canonical journey (what to click)

### Scene 1 — Dashboard (the command center)
Open `http://127.0.0.1:8000`.
- Point out the **KPI row**: Total Assets, Categories, Zones, Panchayats — *all computed live, nothing hard-coded*.
- The **health donut** (ApexCharts) + four status cards: Healthy / Near Expiry / Expired / Unknown, with percentages. Note **Unknown is counted separately** and excluded from the health percentages.
- Emphasize: **every figure is a doorway**. Hover a status card or a breakdown row to reveal the drill arrow.

### Scene 2 — Drill down the hierarchy
- Click **Browse the hierarchy** isn't needed — instead click the **"Zone-wise → North Zone"** breakdown row (a dashboard shortcut). You land on the **Asset List filtered to that zone**, with the filter shown as a removable chip.
- Alternatively demonstrate the full tree: from the breadcrumb **Districts → Salem → North Zone → Erumapalayam Panchayat → Educational Assets**. Each level shows **scoped asset counts**.

### Scene 3 — Asset List (the convergence screen)
- Show it renders **identically regardless of entry path** (drill-down, dashboard shortcut, or search).
- **Search** "school" — live, case-insensitive, matches name and asset number.
- Apply a **Status = Near Expiry** filter from the dropdown; combine with a Zone — point out **AND logic** and the **result count** updating together.
- Remove a chip / **Reset** to restore the list. Resize the window to show the **table → cards** reflow on narrow screens.

### Scene 4 — Asset Detail + sub-views
- Open **Government Primary School (EDU-0001)**.
- Walk the **five information groups**: Administrative · Asset · Location · Lifecycle · Photos.
- Open **Lifecycle detail** — the **life-consumed gauge** and the threshold legend; the status is **computed at runtime, never stored**.
- Open **Photos** — the lightbox (arrow keys / Esc), with placeholders for missing images.
- Open **Location** — the map pin (or coordinate preview). Then open **Jagirammapalayam Community Hall (PUB-0002)** to show **"location unavailable"** and **Unknown** lifecycle (missing inputs) handled gracefully.

### Scene 5 — The architecture promise (the closer)
- The headline: **this UI never reads JSON or computes status itself.** It depends on a service layer behind a contract.
- To go live later we implement **one** `EloquentAssetProvider`, flip `config('ramp.data_provider')` from `mock` to `eloquent`, and **nothing in the UI changes**. That is the entire migration.

---

## 3. Talking points (map to success criteria — docs/01)

| Success criterion | Shown by |
|---|---|
| SC-01 Hierarchy navigable end-to-end | Scenes 2–4 |
| SC-02 Lifecycle status computed correctly | Lifecycle view + boundary tests |
| SC-03 Dashboard reconciles with data | Scene 1 (counts derive from the dataset) |
| SC-04 Search & filter return correct results | Scene 3 |
| SC-05 Asset detail is complete | Scene 4 (five groups) |
| SC-06 Photos & location render | Scene 4 (gallery + map / unavailable) |
| SC-07 Architecture is data-source agnostic | Scene 5 |
| SC-08 Stakeholders approve direction | The ask after the demo |

---

## 4. Edge cases worth showing (they impress)

- **Unknown status** (PUB-0002) — surfaced, not hidden; excluded from health %.
- **No photos** (North Government School) — friendly empty state, not an error.
- **No coordinates** (PUB-0002) — "location unavailable", rest of the record still shown.
- **Zero-count category** — a panchayat with no assets still lists all four categories at count 0.
- **Invalid id in the URL** — redirects to the nearest valid screen with a notice (try `/assets/AST-9999`).

---

## 5. What's intentionally out of scope (Phase 1)

No database, no write operations (create/edit/delete), no authentication, no API, and none of the deferred modules (maintenance, inspections, work orders, notifications, approvals, mobile). The POC validates the concept and the architecture — the upgrade path to each of these is preserved, not built.

---

*End of Demo Script — RAMP-DOC-13*
