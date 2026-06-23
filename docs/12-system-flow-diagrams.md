# System Flow Diagrams — RAMP

| Field | Value |
|---|---|
| Document ID | RAMP-DOC-12 |
| Document Title | System Flow Diagrams |
| Version | 1.0 |
| Status | Draft (POC) |
| Audience | Developers, Architects, Product Owner, Future Claude (AI) sessions |
| Related Documents | 03-FRS, 04-Screen Flow, 07-Business Rules, 08-Mock Data, 10-Claude Development Guide, 11-UI/UX |

---

## 1. Purpose

This document captures the **behavioral and structural flows** of the RAMP POC as Mermaid diagrams. They are the visual companion to the textual specifications in Docs 03, 04, and 07, and to the architecture in Doc 10.

The diagrams cover:

1. **User Journey** — the end-to-end path a user takes through the platform.
2. **Dashboard Navigation** — how Dashboard aggregates drill down into detail.
3. **Asset Lifecycle** — how lifecycle status is computed from raw inputs.
4. **Asset Detail Flow** — the structure and sub-views of a single asset.
5. **Future System Architecture** — how the mock-now / API-later data layer is organized.

> **Rendering note:** These are [Mermaid](https://mermaid.js.org/) diagrams. They render natively in most Markdown viewers (GitHub, VS Code with a Mermaid extension, many docs tools). If a viewer does not render Mermaid, the fenced code is still readable as structured text.

---

## 2. User Journey

The canonical journey moves from the Dashboard down the administrative hierarchy to a single asset, then into that asset's sub-views (Photos, Location, Lifecycle). Search and Filter provide a shortcut directly into the Asset List.

```mermaid
flowchart TD
    Start([User opens RAMP]) --> Dashboard[Dashboard]

    Dashboard -->|Drill down| Zone[Zone View]
    Dashboard -->|Search / Filter| AssetList[Asset List]
    Dashboard -->|Click a KPI / status| AssetList

    Zone --> Panchayat[Panchayat View]
    Panchayat --> Category[Asset Category View]
    Category --> AssetList

    AssetList -->|Select an asset| AssetDetail[Asset Detail]

    AssetDetail --> Photos[Photo Gallery]
    AssetDetail --> Location[Location / Map View]
    AssetDetail --> Lifecycle[Lifecycle View]

    Photos --> AssetDetail
    Location --> AssetDetail
    Lifecycle --> AssetDetail

    AssetDetail -.->|Breadcrumb: up the hierarchy| AssetList
    AssetList -.->|Breadcrumb| Category
    Category -.->|Breadcrumb| Panchayat
    Panchayat -.->|Breadcrumb| Zone
    Zone -.->|Breadcrumb: Home| Dashboard

    classDef hub fill:#1A73E8,stroke:#1765CC,color:#FFFFFF;
    classDef leaf fill:#F5F6F8,stroke:#E0E0E0,color:#202124;
    class Dashboard,AssetList hub;
    class Photos,Location,Lifecycle leaf;
```

**Reading the diagram**

- **Solid arrows** = forward drill-down actions.
- **Dotted arrows** = reverse navigation via breadcrumbs (always available — see Doc 11, §5.2).
- The **Dashboard** and **Asset List** (highlighted) are hubs: multiple paths converge on the Asset List, and every screen can return Home.

---

## 3. Dashboard Navigation

The Dashboard is a set of aggregates, each of which is a **drill-down doorway** into a filtered Asset List. Counts and summaries are produced by the aggregation service (Doc 10) over mock data (Doc 08).

```mermaid
flowchart TD
    subgraph DASH[Dashboard]
        KPI_Total[Total Assets KPI]
        KPI_Cat[Asset Categories KPI]
        KPI_Zone[Zones KPI]
        KPI_Pan[Panchayats KPI]
        Health[Asset Health Summary<br/>Healthy / Near Expiry / Expired / Unknown]
        ByZone[Zone-wise Breakdown]
        ByPan[Panchayat-wise Breakdown]
        ByCat[Category Breakdown]
    end

    KPI_Total -->|All assets| AssetList[Asset List]
    KPI_Cat -->|Grouped by category| CategoryIndex[Category View]
    KPI_Zone -->|Grouped by zone| ZoneIndex[Zone View]
    KPI_Pan -->|Grouped by panchayat| PanchayatIndex[Panchayat View]

    Health -->|Filter: Healthy| AssetList
    Health -->|Filter: Near Expiry| AssetList
    Health -->|Filter: Expired| AssetList
    Health -->|Filter: Unknown| AssetList

    ByZone -->|Select a zone| AssetList
    ByPan -->|Select a panchayat| AssetList
    ByCat -->|Select a category| AssetList

    AssetList -->|Select asset| AssetDetail[Asset Detail]

    classDef hub fill:#1A73E8,stroke:#1765CC,color:#FFFFFF;
    class AssetList hub;
```

**Key rules reflected here**

- Every aggregate (KPI, health segment, breakdown row) leads somewhere — no number is a dead-end (Doc 11, DP-05).
- Selecting a **health status** applies that status as a filter on the Asset List.
- All breakdowns ultimately funnel to the **Asset List**, then to **Asset Detail**.

---

## 4. Asset Lifecycle

Lifecycle status is **always computed, never stored** (Doc 07). This flow shows the exact decision order — including validation of inputs and the boundary conditions (Remaining Life of 5 → Near Expiry; Remaining Life of 0 → Expired).

```mermaid
flowchart TD
    A([Asset record]) --> B{construction_year and<br/>expected_life present<br/>and valid?}

    B -- No --> U[/Status = UNKNOWN<br/>grey/]

    B -- Yes --> C[Current Age = Current Year − Construction Year]
    C --> D[Remaining Life = Expected Life − Current Age]

    D --> E{Remaining Life ≤ 0?}
    E -- Yes --> X[/Status = EXPIRED<br/>red/]

    E -- No --> F{Remaining Life ≤ 5?}
    F -- Yes --> N[/Status = NEAR EXPIRY<br/>amber/]
    F -- No --> H[/Status = HEALTHY<br/>green/]

    classDef expired fill:#D93025,stroke:#A50E0E,color:#FFFFFF;
    classDef near fill:#F9A825,stroke:#C17900,color:#202124;
    classDef healthy fill:#1E8E3E,stroke:#0B6B2C,color:#FFFFFF;
    classDef unknown fill:#80868B,stroke:#5F6368,color:#FFFFFF;
    class X expired;
    class N near;
    class H healthy;
    class U unknown;
```

**Decision order (must be implemented exactly)**

1. **Validate inputs first.** If `construction_year` or `expected_life` is missing or invalid → **Unknown**.
2. Compute **Current Age** = Current Year − Construction Year.
3. Compute **Remaining Life** = Expected Life − Current Age.
4. If **Remaining Life ≤ 0** → **Expired**.
5. Else if **Remaining Life ≤ 5** → **Near Expiry** (this captures the boundary value 5).
6. Else → **Healthy**.

> The boundaries are deliberate: a Remaining Life of exactly **5** is **Near Expiry**, and exactly **0** is **Expired**. See worked examples in Doc 07.

---

## 5. Asset Detail Flow

The Asset Detail screen is the anchor for a single asset. It presents administrative, asset, location, lifecycle, and media information, with three sub-views. Status on this screen is rendered from the lifecycle computation in §4.

```mermaid
flowchart TD
    List[Asset List] -->|Select asset| Detail[Asset Detail Screen]

    subgraph DETAIL[Asset Detail composition]
        Header[Header: Asset Name + Number<br/>+ Status Banner]
        Admin[Administrative Info<br/>State / District / Zone / Panchayat]
        Info[Asset Info<br/>Category / Type / Number]
        Loc[Location Info<br/>Address / Lat / Long]
        Life[Lifecycle Info<br/>Construction Year / Expected Life<br/>+ computed Age, Remaining Life, Status]
        Media[Media: Photo thumbnails]
    end

    Detail --> Header
    Detail --> Admin
    Detail --> Info
    Detail --> Loc
    Detail --> Life
    Detail --> Media

    Loc -->|View on map| MapView[Location / Map View]
    Media -->|View all photos| Gallery[Photo Gallery]
    Life -->|Lifecycle detail| LifecycleView[Lifecycle View]

    MapView -.->|Back| Detail
    Gallery -.->|Back| Detail
    LifecycleView -.->|Back| Detail

    Header -. status sourced from .-> Compute{{Lifecycle computation<br/>see Section 4}}

    classDef compute fill:#F5F6F8,stroke:#80868B,color:#202124,stroke-dasharray: 4 3;
    class Compute compute;
```

**Notes**

- The **status banner** in the header is produced by the shared lifecycle service (§4) — never hard-coded.
- **Location**, **Photos**, and **Lifecycle** open as sub-views and return to Detail via breadcrumb/back.
- All fields map directly to the `Asset` and `Photo` entities defined in Doc 06 and populated per Doc 08.

---

## 6. Future System Architecture

This is the architectural promise of the POC: the **UI depends on a stable data-service interface**, not on the data source. Today a `MockDataProvider` reads JSON; tomorrow an `ApiDataProvider` calls real services backed by a database — **with no UI changes**. The provider is chosen by a factory/config switch (Doc 10).

```mermaid
flowchart TB
    subgraph UI[Presentation Layer]
        Screens[Screens: Dashboard, Zone, Panchayat,<br/>Category, Asset List, Asset Detail,<br/>Map, Photo Gallery]
        Components[Reusable Components:<br/>Cards, Tables, Status Pill, Breadcrumb]
    end

    subgraph DOMAIN[Domain Layer - pure logic]
        Lifecycle[lifecycle service<br/>compute status]
        Aggregation[aggregation service<br/>counts and summaries]
    end

    subgraph SERVICE[Data Service Layer - the stable seam]
        Interface{{AssetDataService Interface}}
        Factory[dataServiceFactory<br/>config-driven selection]
    end

    subgraph PROVIDERS[Providers - swappable]
        Mock[MockDataProvider<br/>Phase 1: reads mock-data/*.json]
        Api[ApiDataProvider<br/>Phase 2+: calls REST APIs]
    end

    subgraph SOURCES[Data Sources]
        Json[(mock-data/*.json<br/>NOW)]
        Backend[REST API + Database<br/>FUTURE]
    end

    Screens --> Components
    Screens --> Interface
    Components --> Interface
    Screens --> Lifecycle
    Screens --> Aggregation
    Aggregation --> Interface

    Factory --> Interface
    Interface --> Mock
    Interface --> Api

    Mock --> Json
    Api --> Backend

    classDef now fill:#1E8E3E,stroke:#0B6B2C,color:#FFFFFF;
    classDef future fill:#80868B,stroke:#5F6368,color:#FFFFFF,stroke-dasharray: 5 4;
    classDef seam fill:#1A73E8,stroke:#1765CC,color:#FFFFFF;
    class Mock,Json now;
    class Api,Backend future;
    class Interface,Factory seam;
```

**How the swap works**

| Element | Phase 1 (now) | Phase 2+ (future) |
|---|---|---|
| Data source | `mock-data/*.json` | REST API + database |
| Provider | `MockDataProvider` | `ApiDataProvider` |
| Interface | `AssetDataService` (unchanged) | `AssetDataService` (unchanged) |
| Selection | `dataServiceFactory` returns Mock | `dataServiceFactory` returns Api |
| UI / Domain | **No change** | **No change** |

**Architectural guarantees**

- The **UI never reads JSON directly** and never calls APIs directly — it only talks to `AssetDataService` (Doc 10).
- **Lifecycle status and aggregations live in the domain layer**, independent of the data source, so they behave identically before and after migration.
- Switching providers is a **configuration change** in the factory — the blue "seam" is the only place that knows which provider is live.
- This is what lets the team build the full POC on mock data today while guaranteeing a low-friction path to APIs and a database later.

---

## 7. Diagram-to-Document Traceability

| Diagram | Primary source documents |
|---|---|
| User Journey (§2) | 04-Screen Flow, 11-UI/UX (navigation rules) |
| Dashboard Navigation (§3) | 03-FRS (Dashboard), 08-Mock Data (aggregation expectations), 11-UI/UX (§6) |
| Asset Lifecycle (§4) | 07-Business Rules (lifecycle + health), 06-Data Model |
| Asset Detail Flow (§5) | 03-FRS (Asset Mgmt), 04-Screen Flow, 06-Data Model |
| Future Architecture (§6) | 10-Claude Development Guide (architecture), 09-Roadmap (phases) |

---

*End of Document — RAMP-DOC-12*
