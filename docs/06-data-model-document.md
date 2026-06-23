# Data Model Document — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-06 |
| Document Title | Data Model Document (Logical, Future-Ready) |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Architects, Developers, Future DBA |
| Related Documents | 03-FRS, 07-Business Rules, 08-Mock Data, 10-Claude Dev Guide |

---

## 1. Introduction

Although the POC implements **no database** and uses **mock JSON**, this document defines the **logical data model** that the mock data conforms to and that a future database/API will implement. Designing the model now ensures the mock JSON has the right shape, so the eventual migration to a database is a matter of mapping the same entities and relationships to tables/resources — not redesigning data structures.

**Principles:**
- Each entity below corresponds to one mock JSON collection today and one database table / API resource tomorrow.
- Identifiers (`id`) are stable keys that act as foreign keys between entities, mirroring future primary/foreign keys.
- **Lifecycle status is NOT stored** — it is derived at runtime (see `07-business-rules.md`). The model stores only the inputs (`construction_year`, `expected_life`).
- Field naming uses `snake_case` for forward compatibility with typical database conventions; the application layer may map these to its own casing.

---

## 2. Entity Overview & Relationships

```
State (1) ──< District (N)
District (1) ──< Zone (N)
Zone (1) ──< Panchayat (N)
Panchayat (1) ──< Asset (N)
AssetCategory (1) ──< Asset (N)
Asset (1) ──< Photo (N)
```

| Relationship | Cardinality | Meaning |
|---|---|---|
| State → District | 1 : N | A state has many districts |
| District → Zone | 1 : N | A district has many zones |
| Zone → Panchayat | 1 : N | A zone has many panchayats |
| Panchayat → Asset | 1 : N | A panchayat has many assets |
| AssetCategory → Asset | 1 : N | A category classifies many assets |
| Asset → Photo | 1 : N | An asset has many photos |

> The administrative hierarchy is a strict tree: each child references exactly one parent.

### 2.1 Entity-Relationship Diagram (Logical)

```
┌───────────┐      ┌────────────┐      ┌──────────┐      ┌──────────────┐
│   State   │1    *│  District  │1    *│   Zone   │1    *│  Panchayat   │
│  id (PK)  │──────│  id (PK)   │──────│ id (PK)  │──────│  id (PK)     │
│  name     │      │  state_id  │      │district_id│     │  zone_id     │
│  code     │      │  name      │      │  name    │      │  name        │
└───────────┘      └────────────┘      └──────────┘      └──────┬───────┘
                                                                │1
                                                                │
                                                                │*
┌──────────────────┐                                    ┌───────▼────────┐
│  AssetCategory   │1                                  *│     Asset      │
│  id (PK)         │───────────────────────────────────│  id (PK)       │
│  name            │                                    │  asset_number  │
│  sub_types[]     │                                    │  category_id   │
└──────────────────┘                                    │  panchayat_id  │
                                                         │  ...           │
                                                         └───────┬────────┘
                                                                 │1
                                                                 │
                                                                 │*
                                                          ┌──────▼──────┐
                                                          │    Photo    │
                                                          │  id (PK)    │
                                                          │  asset_id   │
                                                          │  url        │
                                                          └─────────────┘
```

---

## 3. Entity Definitions

> **Data type conventions:** `String`, `Integer`, `Decimal`, `Boolean`, `Date/Year`, `Array<…>`, `UUID/String` (for ids). In the POC, ids may be human-readable strings (e.g., `"PAN-ERU-001"`); in the database they may become UUIDs or auto-increment integers — the relationships remain identical.

### 3.1 Entity: State

- **Description:** Top of the administrative hierarchy. Represents a state (e.g., Tamil Nadu).
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the state |
| name | String | Yes | State name (e.g., "Tamil Nadu") |
| code | String | No | Optional short code (e.g., "TN") |

- **Relationships:**
  - One State → many Districts (`District.state_id` → `State.id`).

- **Future DB note:** Becomes table `state`; `id` primary key; indexed by `name`/`code`.

---

### 3.2 Entity: District

- **Description:** A district within a state (e.g., Salem).
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the district |
| state_id | String/UUID (FK) | Yes | Parent state reference |
| name | String | Yes | District name (e.g., "Salem") |
| code | String | No | Optional short code |

- **Relationships:**
  - Many Districts → one State.
  - One District → many Zones.

- **Future DB note:** Table `district`; FK `state_id` → `state.id`; index on `state_id`.

---

### 3.3 Entity: Zone

- **Description:** An administrative zone within a district (e.g., North Zone).
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the zone |
| district_id | String/UUID (FK) | Yes | Parent district reference |
| name | String | Yes | Zone name (e.g., "North Zone") |

- **Relationships:**
  - Many Zones → one District.
  - One Zone → many Panchayats.

- **Future DB note:** Table `zone`; FK `district_id` → `district.id`; index on `district_id`.

---

### 3.4 Entity: Panchayat

- **Description:** A panchayat within a zone (e.g., Erumapalayam Panchayat). Direct parent of assets.
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the panchayat |
| zone_id | String/UUID (FK) | Yes | Parent zone reference |
| name | String | Yes | Panchayat name (e.g., "Erumapalayam Panchayat") |

- **Relationships:**
  - Many Panchayats → one Zone.
  - One Panchayat → many Assets.

- **Future DB note:** Table `panchayat`; FK `zone_id` → `zone.id`; index on `zone_id`.

---

### 3.5 Entity: AssetCategory

- **Description:** Classification of assets (Educational, Healthcare, Water Infrastructure, Public Infrastructure), each with sub-types (asset types).
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the category |
| name | String | Yes | Category name (e.g., "Educational Assets") |
| sub_types | Array<String> | Yes | Asset types within the category (e.g., ["Primary School", "Nursery School", "Government School"]) |
| description | String | No | Optional category description |

- **Relationships:**
  - One AssetCategory → many Assets.

- **Future DB note:** Two options at migration time: (a) keep `sub_types` as a normalized child table `asset_type(id, category_id, name)`, or (b) store as an enum/lookup. The POC stores sub-types inline; the FRS already treats `asset_type` as a distinct attribute on Asset, easing normalization.

---

### 3.6 Entity: Asset

- **Description:** The core entity — a single non-movable public asset, with administrative, location, lifecycle, and media information.
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the asset |
| asset_number | String | Yes | Human-facing unique asset number (e.g., "EDU-0001") |
| asset_name | String | Yes | Asset name (e.g., "Govt Primary School") |
| category_id | String/UUID (FK) | Yes | Reference to AssetCategory |
| asset_type | String | Yes | Sub-type within the category (e.g., "Primary School") |
| panchayat_id | String/UUID (FK) | Yes | Reference to Panchayat (resolves zone/district/state by traversal) |
| address | String | No | Human-readable address |
| latitude | Decimal | No | Latitude (−90 to 90) |
| longitude | Decimal | No | Longitude (−180 to 180) |
| construction_year | Integer (Year) | No* | Year the asset was constructed |
| expected_life | Integer | No* | Expected useful life in years |
| created_at | Date/Timestamp | No | (Future) record creation time |
| updated_at | Date/Timestamp | No | (Future) record update time |

\* Required for lifecycle computation; if absent, status is "Unknown" and the asset is flagged incomplete (see `07-business-rules.md`).

- **Derived (NOT stored) fields:**

| Derived Field | Formula |
|---|---|
| current_age | `current_year − construction_year` |
| remaining_life | `expected_life − current_age` |
| status | Healthy (>5) / Near Expiry (0<…≤5) / Expired (≤0) / Unknown (missing inputs) |

- **Relationships:**
  - Many Assets → one Panchayat.
  - Many Assets → one AssetCategory.
  - One Asset → many Photos.

- **Denormalization note (POC convenience):** For ease of display, mock asset records MAY embed resolved labels (`state_name`, `district_name`, `zone_name`, `panchayat_name`, `category_name`) alongside the FK ids. In the database, these are derived via joins; the POC may carry them inline to avoid building a join layer prematurely. The canonical relationship remains via ids.

- **Future DB note:** Table `asset`; FKs `category_id` → `asset_category.id`, `panchayat_id` → `panchayat.id`; indexes on `asset_number` (unique), `panchayat_id`, `category_id`. Lifecycle status computed in a view/service, never stored.

---

### 3.7 Entity: Photo

- **Description:** A photographic record associated with an asset.
- **Fields:**

| Field | Data Type | Required | Description |
|---|---|---|---|
| id | String/UUID (PK) | Yes | Unique identifier for the photo |
| asset_id | String/UUID (FK) | Yes | Parent asset reference |
| url | String | Yes | Image location (mock path/URL now; storage URL later) |
| caption | String | No | Caption/label (e.g., "Front view") |
| sequence | Integer | No | Display order |
| uploaded_at | Date/Timestamp | No | (Future) upload time |

- **Relationships:**
  - Many Photos → one Asset.

- **Future DB note:** Table `photo`; FK `asset_id` → `asset.id`; index on `asset_id`; `url` points to object storage (e.g., blob/S3-style) in production.

---

## 4. Reference Lookups (Status Thresholds)

Lifecycle thresholds are configuration, not data, but are documented here for completeness and to keep them centralized for future externalization:

| Status | Condition (on Remaining Life) |
|---|---|
| Healthy | > 5 years |
| Near Expiry | > 0 and ≤ 5 years |
| Expired | ≤ 0 years |
| Unknown | construction_year or expected_life missing/invalid |

---

## 5. Data Integrity Rules (Logical)

| # | Rule |
|---|---|
| DI-01 | `asset_number` is unique across all assets. |
| DI-02 | Every Asset references a valid `panchayat_id` and `category_id`. |
| DI-03 | Every District/Zone/Panchayat references a valid parent id. |
| DI-04 | `latitude` ∈ [−90, 90]; `longitude` ∈ [−180, 180] when present. |
| DI-05 | `construction_year` ≤ current year; `expected_life` > 0 when present. |
| DI-06 | Every Photo references a valid `asset_id`. |
| DI-07 | `asset_type` must be one of the parent category's `sub_types`. |

> In the POC these are enforced by mock-data discipline and light runtime validation. In the database they become constraints (unique, foreign key, check) and at the API they become validation rules.

---

## 6. Migration Readiness Summary

| Mock JSON Collection (POC) | Future DB Table | Future API Resource |
|---|---|---|
| `states.json` | `state` | `GET /states` |
| `districts.json` | `district` | `GET /districts?state_id=` |
| `zones.json` | `zone` | `GET /zones?district_id=` |
| `panchayats.json` | `panchayat` | `GET /panchayats?zone_id=` |
| `categories.json` | `asset_category` (+ `asset_type`) | `GET /categories` |
| `assets.json` | `asset` | `GET /assets?…filters`, `GET /assets/{id}` |
| `photos.json` (or embedded) | `photo` | `GET /assets/{id}/photos` |

**Key guarantee:** Because the UI consumes data through a service interface keyed on these entities and relationships, replacing the mock provider with API clients (returning the same shapes) requires no change to screens or business logic. See `10-claude-development-guide.md` for the concrete data-layer interface pattern.
