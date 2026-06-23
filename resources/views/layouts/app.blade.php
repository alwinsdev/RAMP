<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title ?? 'RAMP — Rural Asset Management Platform' }}</title>

    {{-- Inter — the brand typeface (privacy-friendly, Laravel's default font host) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-ink">
    {{-- App header — persistent, sticky, frosted (UI_RULES LR-01) --}}
    <header class="sticky top-0 z-40 border-b border-hairline/80 bg-surface/80 backdrop-blur-md" style="box-shadow: var(--shadow-header);">
        <div class="mx-auto flex h-16 max-w-[1280px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" wire:navigate class="group flex items-center gap-3">
                <span class="grid h-9 w-9 place-items-center rounded-xl text-white shadow-sm transition group-hover:scale-105"
                      style="background-image: linear-gradient(140deg, var(--color-brand), var(--color-brand-strong));">
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" />
                    </svg>
                </span>
                <span class="flex flex-col leading-tight">
                    <span class="text-[15px] font-bold tracking-tight text-ink">RAMP</span>
                    <span class="text-[11px] font-medium text-ink-muted">Rural Asset Management Platform</span>
                </span>
            </a>

            <span class="hidden items-center gap-1.5 rounded-full border border-hairline bg-surface-soft px-3 py-1 text-[11px] font-semibold text-ink-soft sm:inline-flex">
                <span class="h-1.5 w-1.5 rounded-full" style="background: var(--color-status-healthy);"></span>
                POC · Mock Data
            </span>
        </div>
    </header>

    {{-- Breadcrumb bar — appears on every screen below the Dashboard (UI_RULES LR-02) --}}
    @isset($breadcrumbs)
        <div class="border-b border-hairline/70 bg-surface/60">
            <div class="mx-auto max-w-[1280px] px-4 py-2.5 sm:px-6 lg:px-8">
                {{ $breadcrumbs }}
            </div>
        </div>
    @endisset

    <main class="mx-auto max-w-[1280px] px-4 py-7 sm:px-6 sm:py-9 lg:px-8">
        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-[1280px] px-4 pb-10 sm:px-6 lg:px-8">
        <p class="border-t border-hairline/70 pt-5 text-xs text-ink-muted">
            RAMP — Proof of Concept · Phase 1 runs on mock data · architected for a zero-rewrite migration to live APIs &amp; database.
        </p>
    </footer>
</body>
</html>
