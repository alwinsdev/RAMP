# Mock Data Specification — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-08 |
| Document Title | Mock Data Specification |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Developers, QA |
| Related Documents | 06-Data Model, 07-Business Rules, 10-Claude Dev Guide |

---

## 1. Introduction

This document defines the **mock JSON data** that powers the RAMP POC in the absence of a database. The data conforms exactly to the logical model in `06-data-model-document.md`. The structures here are the **contract** the data/service layer returns; when the POC later moves to live APIs, the API responses are expected to match these shapes so no UI changes are required.

**Conventions:**
- One collection (JSON array) per entity, stored in a `mock-data/` folder.
- Identifiers are stable, human-readable strings in the POC (e.g., `"PAN-ERU"`), acting as foreign keys.
- Lifecycle status is **NOT** included in mock data — it is computed at runtime (per `07-business-rules.md`). Only `construction_year` and `expected_life` are stored.
- Asset records MAY embed resolved hierarchy/category labels for display convenience (denormalization), while still carrying the canonical FK ids.
- Examples below use Current Year = 2026 for any illustrative status commentary.

**Mock data file layout:**
```
mock-data/
├── states.json
├── districts.json
├── zones.json
├── panchayats.json
├── categories.json
├── assets.json
└── photos.json        (optional; photos may also be embedded in assets)
```

---

## 2. Sample States

`mock-data/states.json`

```json
[
  {
    "id": "TN",
    "name": "Tamil Nadu",
    "code": "TN"
  }
]
```

> The POC focuses on a single state to keep the dataset readable. The model fully supports multiple states.

---

## 3. Sample Districts

`mock-data/districts.json`

```json
[
  {
    "id": "DIST-SALEM",
    "state_id": "TN",
    "name": "Salem",
    "code": "SLM"
  },
  {
    "id": "DIST-ERODE",
    "state_id": "TN",
    "name": "Erode",
    "code": "ERD"
  }
]
```

---

## 4. Sample Zones

`mock-data/zones.json`

```json
[
  { "id": "ZONE-SLM-N", "district_id": "DIST-SALEM", "name": "North Zone" },
  { "id": "ZONE-SLM-S", "district_id": "DIST-SALEM", "name": "South Zone" },
  { "id": "ZONE-SLM-E", "district_id": "DIST-SALEM", "name": "East Zone" },
  { "id": "ZONE-SLM-W", "district_id": "DIST-SALEM", "name": "West Zone" },
  { "id": "ZONE-ERD-N", "district_id": "DIST-ERODE", "name": "North Zone" }
]
```

---

## 5. Sample Panchayats

`mock-data/panchayats.json`

```json
[
  { "id": "PAN-ERU", "zone_id": "ZONE-SLM-N", "name": "Erumapalayam Panchayat" },
  { "id": "PAN-AMM", "zone_id": "ZONE-SLM-N", "name": "Ammapet Panchayat" },
  { "id": "PAN-KON", "zone_id": "ZONE-SLM-S", "name": "Kondalampatti Panchayat" },
  { "id": "PAN-JAG", "zone_id": "ZONE-SLM-E", "name": "Jagirammapalayam Panchayat" },
  { "id": "PAN-VEE", "zone_id": "ZONE-SLM-W", "name": "Veerapandi Panchayat" }
]
```

---

## 6. Sample Asset Categories

`mock-data/categories.json`

```json
[
  {
    "id": "CAT-EDU",
    "name": "Educational Assets",
    "description": "Schools and educational facilities",
    "sub_types": ["Primary School", "Nursery School", "Government School"]
  },
  {
    "id": "CAT-HLT",
    "name": "Healthcare Assets",
    "description": "Health centres and rural health facilities",
    "sub_types": ["Primary Health Centre", "Rural Health Facility"]
  },
  {
    "id": "CAT-WAT",
    "name": "Water Infrastructure",
    "description": "Water storage and supply assets",
    "sub_types": ["Overhead Water Tank", "Underground Water Tank", "Bore Well"]
  },
  {
    "id": "CAT-PUB",
    "name": "Public Infrastructure",
    "description": "Administrative and community buildings",
    "sub_types": ["Panchayat Office", "Community Hall"]
  }
]
```

---

## 7. Sample Asset Records

`mock-data/assets.json`

Each asset carries canonical FK ids (`category_id`, `panchayat_id`) plus optional denormalized display labels. Lifecycle status is **omitted** (computed at runtime). Photos may be embedded (as below) or referenced from `photos.json`.

```json
[
  {
    "id": "AST-0001",
    "asset_number": "EDU-0001",
    "asset_name": "Government Primary School",
    "category_id": "CAT-EDU",
    "category_name": "Educational Assets",
    "asset_type": "Primary School",
    "panchayat_id": "PAN-ERU",
    "panchayat_name": "Erumapalayam Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "12 School Road, Erumapalayam, Salem, Tamil Nadu",
    "latitude": 11.6643,
    "longitude": 78.1460,
    "construction_year": 2010,
    "expected_life": 30,
    "photos": [
      { "id": "PH-0001", "url": "/mock-images/edu0001-front.jpg", "caption": "Front view", "sequence": 1 },
      { "id": "PH-0002", "url": "/mock-images/edu0001-block-a.jpg", "caption": "Block A", "sequence": 2 },
      { "id": "PH-0003", "url": "/mock-images/edu0001-ground.jpg", "caption": "Playground", "sequence": 3 }
    ]
  },
  {
    "id": "AST-0002",
    "asset_number": "EDU-0002",
    "asset_name": "Erumapalayam Nursery School",
    "category_id": "CAT-EDU",
    "category_name": "Educational Assets",
    "asset_type": "Nursery School",
    "panchayat_id": "PAN-ERU",
    "panchayat_name": "Erumapalayam Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "5 Anganwadi Street, Erumapalayam, Salem, Tamil Nadu",
    "latitude": 11.6651,
    "longitude": 78.1472,
    "construction_year": 2016,
    "expected_life": 15,
    "photos": [
      { "id": "PH-0004", "url": "/mock-images/edu0002-front.jpg", "caption": "Front view", "sequence": 1 }
    ]
  },
  {
    "id": "AST-0003",
    "asset_number": "EDU-0003",
    "asset_name": "North Government School",
    "category_id": "CAT-EDU",
    "category_name": "Educational Assets",
    "asset_type": "Government School",
    "panchayat_id": "PAN-ERU",
    "panchayat_name": "Erumapalayam Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "88 Main Road, Erumapalayam, Salem, Tamil Nadu",
    "latitude": 11.6660,
    "longitude": 78.1455,
    "construction_year": 1998,
    "expected_life": 25,
    "photos": []
  },
  {
    "id": "AST-0004",
    "asset_number": "HLT-0001",
    "asset_name": "Erumapalayam Primary Health Centre",
    "category_id": "CAT-HLT",
    "category_name": "Healthcare Assets",
    "asset_type": "Primary Health Centre",
    "panchayat_id": "PAN-ERU",
    "panchayat_name": "Erumapalayam Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "21 Hospital Road, Erumapalayam, Salem, Tamil Nadu",
    "latitude": 11.6638,
    "longitude": 78.1481,
    "construction_year": 2008,
    "expected_life": 40,
    "photos": [
      { "id": "PH-0005", "url": "/mock-images/hlt0001-front.jpg", "caption": "Main building", "sequence": 1 }
    ]
  },
  {
    "id": "AST-0005",
    "asset_number": "WAT-0001",
    "asset_name": "Erumapalayam Overhead Water Tank",
    "category_id": "CAT-WAT",
    "category_name": "Water Infrastructure",
    "asset_type": "Overhead Water Tank",
    "panchayat_id": "PAN-ERU",
    "panchayat_name": "Erumapalayam Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "Near Bus Stand, Erumapalayam, Salem, Tamil Nadu",
    "latitude": 11.6649,
    "longitude": 78.1466,
    "construction_year": 2003,
    "expected_life": 25,
    "photos": []
  },
  {
    "id": "AST-0006",
    "asset_number": "WAT-0002",
    "asset_name": "Ammapet Bore Well #3",
    "category_id": "CAT-WAT",
    "category_name": "Water Infrastructure",
    "asset_type": "Bore Well",
    "panchayat_id": "PAN-AMM",
    "panchayat_name": "Ammapet Panchayat",
    "zone_name": "North Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "Ward 4, Ammapet, Salem, Tamil Nadu",
    "latitude": 11.6502,
    "longitude": 78.1387,
    "construction_year": 2021,
    "expected_life": 5,
    "photos": []
  },
  {
    "id": "AST-0007",
    "asset_number": "PUB-0001",
    "asset_name": "Kondalampatti Panchayat Office",
    "category_id": "CAT-PUB",
    "category_name": "Public Infrastructure",
    "asset_type": "Panchayat Office",
    "panchayat_id": "PAN-KON",
    "panchayat_name": "Kondalampatti Panchayat",
    "zone_name": "South Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "Office Street, Kondalampatti, Salem, Tamil Nadu",
    "latitude": 11.6201,
    "longitude": 78.1502,
    "construction_year": 2012,
    "expected_life": 50,
    "photos": [
      { "id": "PH-0006", "url": "/mock-images/pub0001-front.jpg", "caption": "Office front", "sequence": 1 }
    ]
  },
  {
    "id": "AST-0008",
    "asset_number": "PUB-0002",
    "asset_name": "Jagirammapalayam Community Hall",
    "category_id": "CAT-PUB",
    "category_name": "Public Infrastructure",
    "asset_type": "Community Hall",
    "panchayat_id": "PAN-JAG",
    "panchayat_name": "Jagirammapalayam Panchayat",
    "zone_name": "East Zone",
    "district_name": "Salem",
    "state_name": "Tamil Nadu",
    "address": "Hall Road, Jagirammapalayam, Salem, Tamil Nadu",
    "latitude": 11.6755,
    "longitude": 78.1620,
    "construction_year": null,
    "expected_life": 40,
    "photos": []
  }
]
```

### 7.1 Illustrative Computed Status (NOT stored — for QA reference, Current Year = 2026)

| Asset No. | Construction | Expected Life | Age | Remaining | Status |
|---|---|---|---|---|---|
| EDU-0001 | 2010 | 30 | 16 | 14 | 🟢 Healthy |
| EDU-0002 | 2016 | 15 | 10 | 5 | 🟡 Near Expiry |
| EDU-0003 | 1998 | 25 | 28 | −3 | 🔴 Expired |
| HLT-0001 | 2008 | 40 | 18 | 22 | 🟢 Healthy |
| WAT-0001 | 2003 | 25 | 23 | 2 | 🟡 Near Expiry |
| WAT-0002 | 2021 | 5 | 5 | 0 | 🔴 Expired |
| PUB-0001 | 2012 | 50 | 14 | 36 | 🟢 Healthy |
| PUB-0002 | null | 40 | — | — | ⚪ Unknown |

> This table is a QA aid only; the application derives these values itself.

---

## 8. Optional Separated Photos Collection

If photos are not embedded in assets, use `mock-data/photos.json`:

```json
[
  { "id": "PH-0001", "asset_id": "AST-0001", "url": "/mock-images/edu0001-front.jpg", "caption": "Front view", "sequence": 1 },
  { "id": "PH-0002", "asset_id": "AST-0001", "url": "/mock-images/edu0001-block-a.jpg", "caption": "Block A", "sequence": 2 },
  { "id": "PH-0003", "asset_id": "AST-0001", "url": "/mock-images/edu0001-ground.jpg", "caption": "Playground", "sequence": 3 },
  { "id": "PH-0004", "asset_id": "AST-0002", "url": "/mock-images/edu0002-front.jpg", "caption": "Front view", "sequence": 1 },
  { "id": "PH-0005", "asset_id": "AST-0004", "url": "/mock-images/hlt0001-front.jpg", "caption": "Main building", "sequence": 1 },
  { "id": "PH-0006", "asset_id": "AST-0007", "url": "/mock-images/pub0001-front.jpg", "caption": "Office front", "sequence": 1 }
]
```

> Choose **one** approach (embedded or separated) and apply it consistently. Embedded is simpler for the POC; separated is closer to the future normalized database.

---

## 9. Dashboard Aggregation Expectations (Derived from Mock Data)

These are the values the dashboard should compute from the sample dataset above (illustrative, with the 8 sample assets):

| Metric | Expected (sample) |
|---|---|
| Total Assets | 8 |
| Asset Categories | 4 |
| Zone-wise — North Zone | 6 (EDU-0001/2/3, HLT-0001, WAT-0001, WAT-0002) |
| Zone-wise — South Zone | 1 (PUB-0001) |
| Zone-wise — East Zone | 1 (PUB-0002) |
| Panchayat-wise — Erumapalayam | 5 |
| Panchayat-wise — Ammapet | 1 |
| Healthy | 3 (EDU-0001, HLT-0001, PUB-0001) |
| Near Expiry | 2 (EDU-0002, WAT-0001) |
| Expired | 2 (EDU-0003, WAT-0002) |
| Unknown/Incomplete | 1 (PUB-0002) |

> The full POC dataset should expand to a richer set (e.g., ~100+ assets across all zones/panchayats) so the dashboard demonstrates meaningful distribution. The figures above validate the aggregation logic on the seed sample.

---

## 10. Mock Data Authoring Guidelines

| # | Guideline |
|---|---|
| MD-01 | Keep ids stable and unique; never reuse an id for a different record. |
| MD-02 | Ensure every FK (`state_id`, `district_id`, `zone_id`, `panchayat_id`, `category_id`, `asset_id`) resolves to an existing record. |
| MD-03 | Ensure `asset_type` is a valid sub-type of the asset's category. |
| MD-04 | Do not store lifecycle status; store only `construction_year` and `expected_life`. |
| MD-05 | Include deliberate variety: at least one asset per status (Healthy, Near Expiry, Expired) and at least one Unknown (missing input). |
| MD-06 | Include at least one asset with no photos and one with missing coordinates to exercise empty/unavailable states. |
| MD-07 | Keep coordinates within valid global ranges and roughly plausible for the stated locations. |
| MD-08 | Maintain the same JSON shape the future API will return, so swapping data sources needs no UI change. |
| MD-09 | Validate the dataset (ids, references, ranges) during development to catch integrity errors early. |

---

## 11. Future Migration Note

When migrating from mock JSON to a database/API:
- Each JSON file becomes a table/resource (see `06-data-model-document.md` §6).
- Denormalized labels embedded in assets are replaced by joins/lookups, but the **response shape** delivered to the UI is preserved by the data/service layer.
- Image `url` values move from local mock paths to storage/CDN URLs.
- The QA aggregation expectations (§9) become integration test assertions against the live API.
