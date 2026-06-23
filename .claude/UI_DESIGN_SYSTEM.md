# UI_DESIGN_SYSTEM.md — RAMP Premium Design Language

> **The premium visual layer.** This document defines *how RAMP looks and feels* — an enterprise-grade, calm, data-first aesthetic. It sits **on top of** `UI_RULES.md` / `docs/11`: those define the non-negotiable rules (canonical status colors, breadcrumbs, responsive breakpoints, accessibility); this defines the refined tokens, elevation, typography, and motion that make the product feel premium.
>
> **Precedence:** canonical **status colors and accessibility rules from `UI_RULES.md` always win.** Everything else here is the house style. Tokens live in `resources/css/app.css` (`@theme`); never hard-code these values in components — reference the token utilities.

---

## 1. Design Principles (the premium north star)

| # | Principle | In practice |
|---|---|---|
| P-01 | **Calm, data-first** | Generous whitespace, restrained color, the data is the hero. Color is reserved for status and primary actions. |
| P-02 | **Crisp + soft** | Hairline borders for definition + soft layered shadows for depth. Never heavy/flat or harshly outlined. |
| P-03 | **Quiet motion** | Short (150–220ms), eased transitions on hover/navigation. Motion confirms interaction; it never distracts. |
| P-04 | **One accent** | A single brand blue. No rainbow UI. Status colors are semantic, never decorative. |
| P-05 | **Consistent rhythm** | Everything on an 8px spacing grid; consistent radii and type scale across every screen. |
| P-06 | **Accessible by default** | Status = color + label + dot; visible focus rings; AA contrast; 44px touch targets (inherited from `UI_RULES` §8/§11). |

---

## 2. Color Tokens

### 2.1 Status colors (CANONICAL — locked by `UI_RULES` §3.1)

| Status | Hex | Token |
|---|---|---|
| Healthy | `#1E8E3E` | `--color-status-healthy` |
| Near Expiry | `#F9A825` | `--color-status-near` |
| Expired | `#D93025` | `--color-status-expired` |
| Unknown | `#80868B` | `--color-status-unknown` |

Rendered **only** via `<x-status-badge>` as a premium soft-tint pill: a 12% wash of the status color, a 28% ring, a saturated dot, and the label in the full status color. Detail screens use `size="lg"`.

### 2.2 Neutrals & brand (premium layer)

| Token | Hex | Use |
|---|---|---|
| `--color-canvas` | `#F6F8FB` | App background (with a faint top brand glow) |
| `--color-surface` | `#FFFFFF` | Cards, panels |
| `--color-surface-soft` | `#FBFCFE` | Table headers, inset chips |
| `--color-hairline` | `#E7EBF0` | Borders, dividers |
| `--color-hairline-soft` | `#EEF1F5` | Row dividers |
| `--color-ink` | `#0F172A` | Primary text (slate-900) |
| `--color-ink-soft` | `#5A6473` | Secondary text |
| `--color-ink-muted` | `#8A94A6` | Captions, eyebrows, placeholders |
| `--color-brand` | `#1A73E8` | Primary actions, links, active nav |
| `--color-brand-hover` | `#1765CC` | Hover/pressed primary |
| `--color-brand-strong` | `#1456B8` | Logo gradient end, emphasis |
| `--color-brand-tint` | `#EAF1FD` | Faint blue wash for icon chips / hovers |

> Utilities follow Tailwind v4 token naming: `bg-surface`, `text-ink`, `border-hairline`, `text-brand`, etc. Opacity modifiers (`border-hairline/70`) and `color-mix()` provide tints without new tokens.

---

## 3. Elevation (soft, layered shadows)

| Token | Use |
|---|---|
| `--shadow-card` | Resting state of every card/table/panel |
| `--shadow-raised` | Popovers, active/selected surfaces |
| `--shadow-hover` | Clickable cards on hover (paired with a −2px lift) |
| `--shadow-header` | The sticky app header |

Applied via arbitrary utilities, e.g. `shadow-[var(--shadow-card)]`. **Rule:** interactive surfaces lift on hover (`hover:-translate-y-0.5 hover:shadow-[var(--shadow-hover)]`); static surfaces never do.

---

## 4. Radius & Spacing

- **Radius:** cards/tables/inputs `rounded-xl` (14px); pills `rounded-full`; icon chips `rounded-lg`/`rounded-2xl`. Tokens: `--radius-lg/xl/2xl`.
- **Spacing:** 8px grid (`gap-2/4/6/8`). Card padding `p-5 sm:p-6`. Page gutters `px-4 sm:px-6 lg:px-8`; content max-width **1280px**, centered.
- **Section rhythm:** stacked sections use `gap-8`–`gap-9`; section header = `eyebrow` + bold `text-lg` title.

---

## 5. Typography

- **Face:** **Inter** (loaded via `fonts.bunny.net`), system stack fallback. `--font-sans`.
- **Scale:**

| Role | Class | Notes |
|---|---|---|
| Hero / H1 | `text-3xl sm:text-[2.5rem] font-extrabold tracking-tight` | One per screen |
| Section H2 | `text-lg font-bold tracking-tight` | Paired with an `.eyebrow` |
| KPI number | `text-3xl sm:text-4xl font-extrabold tabular-nums` | Largest type on screen |
| Body | `text-[15px] leading-relaxed` | Default reading size |
| Label / caption | `text-xs`–`text-[13px]` | Metadata, captions |
| Eyebrow | `.eyebrow` | Uppercase, tracked, muted — sits above titles |

- Headings use **tight tracking**; all numerics are **tabular** (`tabular-nums`) for clean column alignment.

---

## 6. Motion

- Transitions: `transition duration-200` with `--ease-premium` where custom easing helps.
- Navigation is SPA-smooth via Livewire `wire:navigate` (no full reloads).
- Tasteful accents only: the header logo scales 1.05 on hover; KPI cards reveal a "View →" affordance on hover; the live status dot uses a gentle `animate-ping`. No gratuitous animation.

---

## 7. Component Patterns (premium specs)

| Component | Premium treatment |
|---|---|
| **App shell** (`layouts/app.blade.php`) | Sticky frosted header (`backdrop-blur`, `--shadow-header`), gradient logo mark, "POC · Mock Data" chip, breadcrumb bar, max-width content, subtle footer note. |
| **Card** (`<x-card>`) | Hairline border + `--shadow-card`; clickable variant lifts to `--shadow-hover`, border tints brand, optional arrow slot. |
| **KPI card** (`<x-kpi-card>`) | Eyebrow label, tinted icon chip, oversized tabular number, hover-reveal "View →". |
| **Status badge** (`<x-status-badge>`) | Soft-tint pill (color-mix wash + ring + dot + label); `sm` for lists, `lg` for the detail banner. |
| **Breadcrumb** (`<x-breadcrumb>`) | Chevron delimiters, hover pill on links, bold current crumb. |
| **Data table** (`<x-data-table>`) | Rounded card container, `surface-soft` uppercase header, hairline row dividers, result-count line; collapses to cards under `sm` (MR-01). |
| **Empty state** (`<x-empty-state>`) | Tinted icon medallion, generous padding, optional action slot — never a dead end. |

---

## 8. Do / Don't

**Do** — use tokens; keep one accent; lift interactive cards on hover; pair every status color with a label; keep sections on the 8px rhythm; let data breathe.

**Don't** — hard-code hex values in components; use status colors for non-status meaning; add heavy drop shadows or thick borders; introduce a second brand color; animate without purpose; shrink body text below 14px.

---

## 9. Implementation pointers

- Tokens & base styles: `resources/css/app.css` (`@theme`, `@layer base/components`).
- Font: `<link>` in `resources/views/layouts/app.blade.php`.
- All primitives: `resources/views/components/*.blade.php`.
- After editing Blade classes, **rebuild assets** (`npm run build`) so Tailwind v4 rescans utilities.

---

*End of UI_DESIGN_SYSTEM.md — the house style. Canonical rules remain in `UI_RULES.md`.*
