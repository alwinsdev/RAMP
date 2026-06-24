# Future Roadmap — RAMP (Phase 2 & Phase 3)

| Field | Value |
|---|---|
| Document | Future Roadmap |
| Audience | Project Sponsor, Solution Architect, Delivery Lead |
| Basis | The POC was architected so production capability is added **incrementally**, not by rewrite |
| Guiding promise | The UI and domain services do not change when the data source changes — only the provider behind the contract changes, by configuration |

---

## 0. Where the POC leaves off
Phase 1 delivered a complete, role-secured, demo-ready application on **mock JSON**, behind a clean seam:

```
Livewire → Services → Contracts (the seam) → Mock Providers → mock JSON
```

The roadmap below replaces what sits **behind the seam**, and then layers on new capability — with the screens and business logic untouched at each step.

---

## Phase 2 — API Enablement (replace mock with live read APIs)
**Goal:** the same UI, backed by real read data.

| Workstream | Detail |
|---|---|
| **Backend read APIs** | Stand up REST endpoints that return the documented resource shapes (districts, zones, panchayats, categories, assets, photos). |
| **`Eloquent`/`Api` data provider** | Implement `AssetDataProvider` / `DashboardDataProvider` against the live source, returning the **same DTO shapes**. |
| **Config switch** | Flip `RAMP_DATA_PROVIDER` from `mock` to the new provider in `DataLayerServiceProvider`. |
| **Acceptance test** | Verify **zero UI/service changes** were required — the proof the POC was built correctly. |
| **Asset Intelligence Map data** | The flagship full-screen map is already built (Phase 1: markers, clustering, heatmap, filters, auto-focus). Phase 2 simply supplies its markers from the live API behind the same `mapMarkers()` seam — **no UI change**. |
| **Reporting & export** | PDF/Excel asset & health reports by district/zone/panchayat (a new deferred capability — distinct from the map). |
| **Asset list scaling** | Server-side pagination, sorting, and search delegated to the API. |
| **Accessibility & device QA** | Formal WCAG AA audit, keyboard/screen-reader pass, cross-device testing. |

**Exit criteria:** the application runs unchanged on live read data; the Asset Intelligence Map renders live markers; list scales; accessibility audited.
**Risk profile:** Low — the seam and DTO shapes are fixed; mapping happens only at the data layer.

---

## Phase 3 — Database & Persistence (enable editing)
**Goal:** an editable, persistent system of record.

| Workstream | Detail |
|---|---|
| **Database schema** | Each logical entity → one table (1:1 with the mock collections); stable ids → primary/foreign keys; relationships unchanged. |
| **Eloquent models & repositories** | Behind the same `…DataProvider` contracts. |
| **Write operations** | `create / update / delete` for assets, categories, photos, and hierarchy nodes — added as **new methods** behind the seam; existing read paths unchanged. |
| **Validation & constraints** | Unique `asset_number`, valid parent references, coordinate ranges, lifecycle inputs become DB constraints / form requests (mirroring the POC's runtime checks). |
| **Derived-value rule preserved** | Lifecycle status remains **computed, never stored**. |
| **Media storage** | Photo URLs move from local paths to object storage (S3-style/CDN). |

**Exit criteria:** officials can create/edit/delete records; data persists; integrity enforced at the database.
**Risk profile:** Medium — first introduction of write flows, migrations, and storage; mitigated by the unchanged read architecture.

---

## Phase 4 — Functional Expansion (deferred modules)
Layered on the established data layer and navigation patterns:
- **Maintenance Management** · **Inspection Management** · **Work Orders** (especially for near-expiry/expired assets).
- **Notifications / Alerts** when assets cross health thresholds.
- **Approval Workflows** for asset changes.

## Phase 5 — Hardening & Mobile
- **Production AuthN/AuthZ + RBAC** aligned to the administrative hierarchy (replacing the mock auth), session security, audit logging.
- **Advanced analytics** — trends, predictive lifecycle forecasting, and advanced geospatial layers (time-aware health playback, density analytics) built on the existing Asset Intelligence Map.
- **Native mobile field app** for on-site capture (GPS + photos).

---

## Architectural enablers already in place (why this is low-risk)
| Enabler | Built in Phase 1 |
|---|---|
| Stable data seam (contracts) | ✅ `AssetDataProvider` / `DashboardDataProvider` |
| Config-driven provider selection | ✅ `DataLayerServiceProvider` + `RAMP_DATA_PROVIDER` |
| Centralised, computed lifecycle | ✅ single `LifecycleCalculator` (status never stored) |
| Single aggregation service | ✅ `DashboardService` (no hard-coded counts) |
| Role-based data visibility | ✅ `Scope` applied in services — extends to production RBAC |
| Stable DTO shapes = API contract | ✅ documented entity shapes |
| Reusable component system | ✅ premium UI primitives |
| Security baseline | ✅ headers/CSP, route constraints, audit |

---

## Indicative sequencing
```
Phase 2  ──►  Phase 3  ──►  Phase 4  ──►  Phase 5
(read APIs,   (DB + write   (modules)     (prod auth,
 live map      ops)                        mobile,
 data, a11y,                              analytics)
 paging)
```
Each phase is independently shippable and adds value without destabilising the prior one.

## Recommended immediate next step
On stakeholder sign-off, begin **Phase 2** with the API contract workshop (confirm endpoint shapes against the documented DTOs), then implement and config-swap the provider — targeting the "zero UI change" acceptance test as the milestone that de-risks the rest of the programme.

*End of Future Roadmap.*
