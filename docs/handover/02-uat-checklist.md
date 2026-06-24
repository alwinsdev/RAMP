# User Acceptance Testing (UAT) Checklist — RAMP POC

| Field | Value |
|---|---|
| Document | UAT Checklist |
| Audience | QA Lead, Product Owner, Business reviewers |
| Scope | POC on mock data (read-only; no create/edit/delete) |
| Method | Manual click-through; record Pass / Fail / N/A + notes |
| Reference | Mirrors the automated suite (94 tests) — manual confirmation for sign-off |

**How to use:** Tester, date, environment ____________________. Mark each row P/F. A Fail must be logged with steps to reproduce. Sign-off requires all **Demo-Critical** rows = Pass.

Legend: ⭐ = Demo-Critical.

---

## 1. Authentication & Access (RBAC)
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | AU-01 | Visit any URL while signed out | Redirected to `/login` | |
| ⭐ | AU-02 | Sign in with valid admin credentials | Lands on Dashboard | |
| | AU-03 | Sign in with wrong password | Inline error; stays on login | |
| | AU-04 | "Forgot password" → submit email | Acknowledgement + link to reset (mock) | |
| | AU-05 | Reset password form validates (min 8, match) | Shows demo confirmation; returns to login | |
| ⭐ | AU-06 | Log out from the user menu | Returns to `/login`; protected pages inaccessible | |
| ⭐ | AU-07 | Sign in as **District Officer** | Dashboard shows **Salem only** (Erode absent) | |
| ⭐ | AU-08 | Sign in as **Panchayat Officer** | Dashboard shows **Erumapalayam only**; sidebar trimmed | |
| ⭐ | AU-09 | As an officer, open an out-of-area asset URL | Redirected to Asset List (no data leak) | |

## 2. Dashboard
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | DA-01 | Dashboard loads as landing page | 7 KPIs, district cards, distribution, health, recent | |
| | DA-02 | KPI totals reconcile | District-card asset counts sum to Total Assets | |
| ⭐ | DA-03 | Click a hierarchy KPI / district card | Navigates into the hierarchy (Districts / Zones) — **not** the Asset List | |
| | DA-04 | Click a health status KPI | Opens Asset List filtered by that status | |
| | DA-05 | Recent assets list | Newest 5 shown; each row opens Asset Information | |
| | DA-06 | Category distribution | All 10 categories shown with proportional bars | |
| | DA-07 | Lifecycle health donut | Healthy/Near/Expired/Unknown with counts + % | |
| ⭐ | DA-08 | Asset Intelligence Map (below KPIs) | Colour-coded markers render (or graceful fallback); "Open full map" link present | |

## 3. Hierarchy Navigation
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | HN-01 | Districts → Zones → Panchayats | Each level shows child cards with counts | |
| | HN-02 | Breadcrumb on every screen below dashboard | Reflects the full path; crumbs navigate up | |
| | HN-03 | Counts at each level | Match the underlying data (sum reconciles) | |
| | HN-04 | Invalid id in URL (e.g. bad zone) | Redirects to a safe parent with a notice | |

## 4. Panchayat Category Dashboard (operational screen)
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | PC-01 | Open a panchayat | Exactly **10 category cards** displayed | |
| ⭐ | PC-02 | Each card shows | Icon, name, Total, Healthy, Near Expiry, Expired, health bar | |
| | PC-03 | Colour coding | Green/Amber/Red counts correct | |
| | PC-04 | Roll-up strip | Totals + **Asset Health Score %** shown | |
| | PC-05 | Zero-count category | Still shown (count 0), still clickable | |
| ⭐ | PC-06 | Click a category card | Opens Asset List filtered to panchayat + category | |
| | PC-07 | Reconciliation | Sum of category totals = panchayat total | |

## 5. Asset List — Search & Filter
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | AL-01 | List renders | Table on desktop; cards on mobile | |
| | AL-02 | Search by name/number | Case-insensitive, trimmed, substring | |
| | AL-03 | Filters (zone/panchayat/category/type/status) | AND logic; result count updates | |
| | AL-04 | Panchayat options constrained by zone | Changing zone clears incompatible panchayat | |
| | AL-05 | Active filter chips + Reset | Removable chips; reset restores full list | |
| | AL-06 | No matches | Friendly "no results" empty state with reset | |
| | AL-07 | Status pill per row | Canonical colour + label | |

## 6. Asset Information
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | AI-01 | Open an asset | Shows Name, Number, Category, Panchayat, Zone, District | |
| ⭐ | AI-02 | Asset Health card | Construction Year, **Asset Age**, Remaining Life, **Health Status** | |
| ⭐ | AI-03 | Lifecycle progress bar | "X / 25 Years Used", colour-coded | |
| | AI-04 | Quick actions | View on Map · View Photos · Asset Health present | |
| | AI-05 | Unknown-lifecycle asset | Status Unknown; figures show "—"; no crash | |

## 7. Location Experience
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| | LO-01 | Embedded map preview (220px) | Marker shown (or graceful fallback) | |
| ⭐ | LO-02 | "View on Map" → full screen | Large map + asset info panel + breadcrumb | |
| | LO-03 | Directions / Open in Google Maps | Open Google Maps in a new tab at the asset | |
| | LO-04 | Copy Coordinates | Copies "lat, lng"; shows "Copied!" | |
| ⭐ | LO-05 | Asset with no coordinates | "Location unavailable"; actions hidden; address still shown | |

## 7b. Asset Intelligence Map (flagship)
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | IM-01 | Open **Map View** from sidebar | Full-screen map; all scoped assets plotted (or graceful fallback) | |
| ⭐ | IM-02 | Marker colours | Green = Healthy, Amber = Near Expiry, Red = Expired, Grey = Unknown | |
| | IM-03 | Zoom out | Markers cluster; clusters split again on zoom-in | |
| ⭐ | IM-04 | Filters (District/Zone/Panchayat/Category/Status) | Marker set narrows; map auto-focuses the selected area | |
| | IM-05 | Heatmap toggle | Switches to a concentration view and back | |
| ⭐ | IM-06 | Click a marker | Card shows name, number, category, panchayat, health, year, remaining life + **Open Asset** | |
| | IM-07 | Open Asset from marker | Navigates to that asset's Information screen | |
| | IM-08 | Reset filters | Clears all five filters; full marker set returns | |
| ⭐ | IM-09 | RBAC scope (Panchayat Officer) | Only the officer's own panchayat assets appear on the map | |

## 8. Photos
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | PH-01 | Asset with photos | Thumbnail grid renders | |
| | PH-02 | Click a thumbnail | Modal lightbox opens (prev/next, caption, Esc) | |
| | PH-03 | Broken/missing image | Placeholder shown, not a broken element | |
| | PH-04 | Asset with no photos | Friendly empty state | |

## 9. Lifecycle / Asset Health
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| ⭐ | LC-01 | Boundary RL = 5 | Status = Near Expiry | |
| ⭐ | LC-02 | Boundary RL = 0 | Status = Expired | |
| | LC-03 | Missing construction year | Status = Unknown | |
| | LC-04 | Same status everywhere | Dashboard, list, information, health all agree | |

## 10. Cross-cutting (UX / Responsive / A11y)
| ⭐ | ID | Check | Expected result | P/F |
|---|---|---|---|---|
| | XC-01 | Friendly language | "Asset Information", "Asset Health", "Asset Age" used | |
| ⭐ | XC-02 | Mobile layout | Sidebar→drawer; cards reflow 1-col; no horizontal scroll | |
| | XC-03 | Sidebar collapse | Icon-only mode persists across navigation | |
| | XC-04 | Keyboard focus | Visible focus ring on interactive elements | |
| | XC-05 | Status accessibility | Colour always paired with a text label | |
| | XC-06 | No dead-ends | Every screen offers a forward action or breadcrumb | |

---

## Sign-off
| Role | Name | Result (Approve / Reject) | Date |
|---|---|---|---|
| QA Lead | | | |
| Product Owner | | | |
| Business reviewer | | | |

*End of UAT Checklist.*
