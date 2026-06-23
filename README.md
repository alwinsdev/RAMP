# Rural Asset Management Platform (RAMP)

A centralized system for managing and monitoring **non-movable public assets** owned by Panchayat & Rural Development Departments, such as schools, health centres, water tanks, panchayat offices, and community halls.

---

## 🚀 Project Overview

RAMP provides administrators with a single, trusted window to track how many assets exist, where they are, and monitor their lifecycle health conditions. 

This is a **Phase 1 Proof of Concept (POC)** built on **Laravel 12** and **Livewire 3**. It operates entirely on mock JSON data without a database dependency, yet it is designed with a strict architectural boundary ("The Seam") that allows swapping in live database/API engines in future phases with **zero UI or component code changes**.

### 🌟 Key Highlights
- **Modern Reactive UI:** Built with Blade, Livewire 3, Alpine.js, and Tailwind CSS v4. No complex React/Vue or TypeScript compilation required.
- **The Seam (Swappable Data Layer):** Abstraction contracts ensure that swapping the mock provider for a live Eloquent/API provider in Phase 2 requires only a config flip.
- **Runtime Computed Lifecycle:** Lifecycle health status is computed dynamically from inputs (`construction_year` and `expected_life`) rather than stored, ensuring a single source of truth.
- **Premium Design System:** Calmed, data-first enterprise look utilizing Inter typography, soft layered elevation, and accessibility-compliant status indicators.
- **Robust Test Coverage:** Built-in PHPUnit tests covering boundary status calculations, search filter combinations, and aggregate reconciliations with no database dependencies.

---

## 🛠️ Technology Stack

| Component | Choice |
|---|---|
| **Backend Framework** | Laravel 12 (PHP 8.2+) |
| **Frontend Framework** | Blade + Livewire 3 (Alpine.js is bundled with Livewire) |
| **Styling** | Tailwind CSS v4 (CSS-only config in `resources/css/app.css`) |
| **Charts & Visualizations** | ApexCharts |
| **Maps** | Google Maps JavaScript API (gracefully degrades if key is missing) |
| **Testing Engine** | PHPUnit 11 |
| **Data Engine** | Mock JSON datasets in `storage/app/mock-data/` mapped to PHP DTOs |

---

## 📦 Project Architecture & Layers

The codebase uses a strict separation of concerns to support future API/DB migrations:

```
app/
├── Contracts/          # The Seam: Provider interfaces (AssetDataProvider, DashboardDataProvider)
├── DataProviders/      # Data Layer: Read raw JSON from disk and maps them to DTOs
│   └── Concerns/       # ReadsMockJson utility trait
├── Services/           # Domain Layer: Handles all aggregations, search, and category listing
├── Support/            # Pure Helpers: Boundary calculators (LifecycleCalculator, AssetFilter)
├── Enums/              # PHP Enums: LifecycleStatus (contains labels and CSS color codes)
├── DataObjects/        # Final readonly DTOs enforcing type contracts (e.g. AssetData, CategoryData)
├── Livewire/           # View Orchestrators: full-page Livewire components
└── Providers/          # DataLayerServiceProvider: Composition root mapping config -> providers
```

### Flow of Data
```
Livewire Component ➔ Domain Service ➔ Data Provider Contract ➔ Mock Provider ➔ JSON Files
```

---

## 🧮 Business Rules & Lifecycle Health

Status is determined at runtime based on the **Remaining Life (RL)** calculation:
$$\text{Remaining Life} = \text{expected\_life} - (\text{current\_year} - \text{construction\_year})$$

- 🟢 **Healthy:** Remaining Life $> 5$ years.
- 🟡 **Near Expiry:** Remaining Life $> 0$ and $\le 5$ years (Boundary $5$ inclusive).
- 🔴 **Expired:** Remaining Life $\le 0$ years (Boundary $0$ inclusive).
- ⚪ **Unknown:** Inputs are missing, negative, or construction year is in the future.

---

## ⚙️ Installation & Setup

### Prerequisites
Ensure you have the following installed on your system:
- **PHP 8.2** or higher
- **Composer**
- **Node.js** and **NPM**

### Setup Steps
1. Clone the repository and navigate into the root directory:
   ```bash
   cd RAMP
   ```

2. Run the automated setup command. This installs dependencies, copies `.env`, generates application keys, and compiles frontend assets:
   ```bash
   composer run setup
   ```

3. *(Optional)* Add your Google Maps API Key in `.env` to enable map panels:
   ```env
   GOOGLE_MAPS_API_KEY=your_google_maps_key
   ```

---

## 💻 Development Commands

The project includes custom Composer scripts defined in `composer.json` to ease daily development tasks:

### Start Development Server
Starts the Laravel web server, queue listener, Vite hot-reload compiler, and tail logs simultaneously:
```bash
composer run dev
```

### Run Test Suite
Executes the PHPUnit tests (no database required):
```bash
composer run test
```
*Or via standard Artisan:*
```bash
php artisan test
```

---

## 🗺️ POC Sprints & Roadmap

- **Sprint 0 (Completed) ✅:** Scaffolded Laravel 12 + Livewire 3. Setup mock filesystem disk, seeded mock JSON data. Implemented DTOs, LifecycleCalculator with 17 boundary tests, services, and the AppShell UI layout.
- **Sprint 1 (Planned) 📅:** Hierarchy screens navigation (ZoneList, PanchayatList, CategoryList), Asset List with status badges, and URL state filter preservation (`#[Url]` tracking).
- **Sprint 2 (Planned) 📅:** Asset Detail view with five info groups, interactive Photo Gallery lightbox, Google Maps location display, and ApexCharts lifecycle progress bar.
- **Sprint 3 (Planned) 📅:** Interactive Landing Dashboard with category/zone breakdown widgets, ApexCharts health distributions, and advanced Search/Filter chips bar.
- **Sprint 4 (Planned) 📅:** UI Polish, responsive layouts (converting tables to mobile cards), asset loading/empty states, and full QA review.

---

## ✉️ Developer Contact

For issues, contributions, or architecture reviews, please contact the developer:

- **Alwin** — [alwins.dev@gmail.com](mailto:alwins.dev@gmail.com)
