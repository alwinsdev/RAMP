# RAMP — POC Handover Package

> Rural Asset Management Platform (RAMP) · Proof of Concept · Government of Tamil Nadu (mock data)
> Stack: Laravel 12 · PHP 8.2+ · Livewire 3 · Alpine · Tailwind v4 · ApexCharts · Google Maps JS
> Status: **POC complete · 94 automated tests passing** · demo-ready (subject to the Pre-Demo Configuration checklist)

This folder contains the client-facing and internal handover documents for the RAMP POC.

| # | Document | Audience | Purpose |
|---|---|---|---|
| 01 | [Stakeholder Demo Script](01-stakeholder-demo-script.md) | Sponsors, Product Owner, Presenter | The exact click-path and talking points for a 12–15 min demo |
| 02 | [UAT Checklist](02-uat-checklist.md) | QA, Product Owner, Business users | Pass/fail acceptance checks across all modules |
| 03 | [Demo Data Validation Checklist](03-demo-data-validation-checklist.md) | QA, Developer | Verifies the mock dataset is correct and demo-safe |
| 04 | [Pre-Demo Configuration Checklist](04-pre-demo-configuration-checklist.md) | Developer, Presenter | Everything to set up before the demo (incl. Google Maps) |
| 05 | [Deployment Checklist](05-deployment-checklist.md) | DevOps, Solution Architect | Steps to stand up the POC on a shared/host environment |
| 06 | [POC Completion Report](06-poc-completion-report.md) | Sponsors, Steering Committee | What was delivered, against objectives |
| 07 | [Future Roadmap (Phase 2 & 3)](07-future-roadmap.md) | Sponsors, Architect, Delivery Lead | The path from POC to production |

**Conventions used across these documents**
- **Demo accounts** (password `password`): `admin@ramp.gov.in` (Administrator) · `district@ramp.gov.in` (District Officer — Salem) · `panchayat@ramp.gov.in` (Panchayat Officer — Erumapalayam).
- **Hierarchy:** District → Zone → Panchayat → Asset Category → Asset (no State level).
- **Dataset:** 100 assets · 10 categories · 13 panchayats · 5 zones · 2 districts (Salem, Erode). Counts are computed live and may shift by ±1 with the runtime year.
- **Lifecycle:** fixed Expected Life = 25 years; Healthy (RL > 5) · Near Expiry (0 < RL ≤ 5) · Expired (RL ≤ 0) · Unknown (missing/invalid inputs).

*Prepared by the delivery team acting as Product Owner, QA Lead, and Solution Architect.*
