# Stakeholder Demo Script — RAMP POC

| Field | Value |
|---|---|
| Document | Stakeholder Demo Script |
| Audience | Project Sponsor, Product Owner, District/Panchayat officials, Steering Committee |
| Duration | 12–15 minutes (+ 5 min Q&A) |
| Environment | POC build on mock data — `php artisan serve` → http://127.0.0.1:8000 |
| Pre-requisite | Complete [Pre-Demo Configuration Checklist](04-pre-demo-configuration-checklist.md) first (maps are keyless OSM — just needs internet) |

> **Goal of the demo:** show that an official can, within seconds and without training, see *how many* public assets exist, *where* they are, and *which need attention* — and that the platform is built to grow into a production system without rework.

---

## 0. Setup (before the audience arrives)
- Server running; browser at the **Login** screen (signed out).
- Two browser profiles/tabs ready if you want a fast role switch: one to log in as Administrator, one as Panchayat Officer.
- Zoom level 100%; window maximised; phone or narrow-window ready for the responsive moment.

---

## 1. Opening — the problem & the login (1 min)
**Say:** *"Today, information about rural public assets — schools, water tanks, ration shops, panchayat buildings — is scattered across registers and spreadsheets. RAMP brings it into one trusted, hierarchy-aware window with health visible at a glance. This is a proof of concept on representative data."*

**Do:** Show the **Login** screen — government-portal branding, "Government of Tamil Nadu", Track · Monitor · Plan · Maintain.
- Sign in as **Administrator** (`admin@ramp.gov.in` / `password`).

---

## 2. The Dashboard — answer three questions instantly (3 min)
**Say:** *"The dashboard answers three questions at a glance: how many, where, and what needs attention."*

**Do / point out:**
- **7 KPIs** — Total Assets, Districts, Zones, Panchayats, and Healthy / Near Expiry / Expired (colour-coded).
- **Asset Intelligence Map** — directly below the KPIs, every asset plotted and colour-coded by health (green / amber / red / grey), clustered when zoomed out. *"This is the flagship view — where the assets are and which need attention, on one map."*
- **District cards** — Salem and Erode, each with zone/panchayat/asset counts and a mini health bar. *"Every number is computed live from the data — nothing is hard-coded."*
- **Asset distribution** by the 10 categories, **Lifecycle Health** donut, and **Recent assets**.

**Key message:** *"The dashboard is hierarchy-first — you start from a district and drill down. The only shortcut is by health status, because 'show me everything that's expired' is what an officer needs most."*

---

## 3. Drill down the hierarchy (3 min)
**Do:** Click the **Salem** district card → **Zones** → click **North Zone** → **Panchayats** → click **Erumapalayam Panchayat**.

**Land on the Panchayat Category Dashboard** — the primary operational screen.
**Say:** *"This is where a panchayat officer lives. Ten large cards — one per asset category — each showing the total and the health breakdown, colour-coded green, amber, red. The strip at the top gives a single Asset Health Score for the whole panchayat."*

**Point out:** the breadcrumb shows the full path; a zero-count category (e.g. Toilet Buildings) is still shown.

---

## 4. From a category to an asset (2 min)
**Do:** Click the **Primary Schools** card → lands on the **Asset List** filtered to that panchayat + category (chips show the active filters).
- Show **Search** (type "school") and the **filters** (status, etc.) briefly.
- Click **Government Primary School, Erumapalayam**.

**On the Asset Information screen, point out:**
- Asset details + administrative info (District / Zone / Panchayat).
- **Asset Health** card with the **lifecycle progress bar** — *"16 of 25 years used"* — colour-coded by status.
- **Location** card with the embedded map preview + address + coordinates.
- **Photos** — click a thumbnail to open the modal lightbox (real photos of government buildings).

---

## 5. Location experience (1.5 min)
**Do:** Click **View on Map** → the full Location screen.
**Say:** *"A larger interactive map, an asset information panel, and one-click actions — Directions, Open in Google Maps, and Copy Coordinates — so a field officer can navigate straight to the asset."*

> The map uses free OpenStreetMap tiles (no API key). If tiles are slow, it's just network latency; the pin and coordinates are always correct.

---

## 5b. Asset Intelligence Map — the flagship (2 min)
**Do:** Open **Map View** from the sidebar → the full-screen **Asset Intelligence Map**.
**Say:** *"This is the primary planning view. Every asset across the state, colour-coded by health — green healthy, amber near expiry, red expired, grey unrated. Markers cluster when you zoom out and split apart as you zoom in."*

**Point out:**
- The **filter bar** — District, Zone, Panchayat, Asset Category, Health Status. *"Pick Salem → North Zone and the map focuses automatically on that area."*
- **Heatmap** toggle — *"switch to a concentration view to see where assets are densest."*
- Click any marker → a card with **name, number, category, panchayat, health, construction year, remaining life**, and an **Open Asset** button straight into the asset.

**Key message:** *"One screen to answer 'where are my assets and which need attention' — for a whole district or a single panchayat."*

---

## 6. Role-based access — the trust story (2 min)
**Do:** Sign out → sign in as **Panchayat Officer** (`panchayat@ramp.gov.in` / `password`).
**Point out:**
- The **sidebar is trimmed** (no District/Zone/Panchayat browsing).
- The **dashboard now shows only Erumapalayam** — ~11 assets, one district, one panchayat. *"Each officer sees only their own area. A district officer sees only their district; an administrator sees everything."*

**Key message:** *"Access control is real, enforced in the service layer — not just hidden in the UI."*

---

## 7. Mobile & close (1.5 min)
**Do:** Narrow the window (or show on a phone) — the sidebar collapses to a drawer, KPI and category cards reflow to a single column.
**Say:** *"Fully usable from a phone in the field."*

**Closing message:** *"Everything you've seen runs on mock data behind a clean service layer. To go live, we replace that data layer with real APIs and a database — with no change to these screens. That is the core promise of how this POC was built: low-risk, incremental growth to production."*

---

## Demo flow at a glance
```
Login (Admin) → Dashboard (KPIs + Asset Intelligence Map) → Salem card → North Zone → Erumapalayam
   → Primary Schools card → Asset List → Government Primary School
      → Asset Health (progress bar) · Location (map + Directions) · Photos (lightbox)
→ Map View (full Asset Intelligence Map — filters + heatmap + marker → Open Asset)
→ Logout → Login (Panchayat Officer) → scoped Dashboard + trimmed sidebar
→ Mobile view → Close
```

## Backup talking points (for Q&A)
- *"Is this real data?"* — Representative: real Tamil Nadu localities, accurate coordinates, real category photos; the per-asset specifics are illustrative. Production uses authoritative records.
- *"How is health calculated?"* — Construction year vs a fixed 25-year expected life; status is computed at runtime, never stored, in one place.
- *"How long to production?"* — Phase 2 swaps the data layer for live read APIs (no UI changes); Phase 3 adds the database and editing. See the [Future Roadmap](07-future-roadmap.md).
- *"Security?"* — Mock auth for the POC; baseline security headers and access scoping already in place; production-grade auth/RBAC is Phase 5.

## Do / Don't on the day
- **Do** keep to the click-path above; lead with totals; let every number be a doorway.
- **Do** showcase the **Asset Intelligence Map** (dashboard + full Map View) — it's the flagship; use the filters and heatmap to tell the "where & what needs attention" story.
- **Do** pre-load the Map View once so the OpenStreetMap tiles are warm; maps are keyless, so there's nothing to configure — just ensure the machine has internet.

*End of Demo Script.*
