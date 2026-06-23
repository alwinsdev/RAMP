<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>{{ $title ?? 'RAMP — Rural Asset Management Platform' }}</title>

    {{-- Favicons (generated from public/images/ramp-logo.png by `php artisan ramp:favicons`) --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">

    {{-- Inter — the brand typeface (privacy-friendly, Laravel's default font host) --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-ink">
    {{-- App header — persistent, sticky, frosted (UI_RULES LR-01) --}}
    <header class="sticky top-0 z-40 border-b border-hairline/80 bg-surface/80 backdrop-blur-md" style="box-shadow: var(--shadow-header);">
        <div class="mx-auto flex h-16 max-w-[1280px] items-center justify-between gap-3 px-4 sm:px-6 lg:px-8">
            <a href="{{ url('/') }}" wire:navigate class="group flex items-center transition group-hover:opacity-90">
                {{-- Brand logo image. Falls back to the text wordmark if the file isn't present yet. --}}
                <img src="{{ asset('images/ramp-logo.png') }}" alt="RAMP — Rural Asset Management Platform"
                     class="h-11 w-auto sm:h-12" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                <span class="hidden items-center gap-3" aria-hidden="true">
                    <span class="grid h-9 w-9 place-items-center rounded-xl text-white shadow-sm" style="background-image: linear-gradient(140deg, var(--color-brand), var(--color-brand-strong));">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" /></svg>
                    </span>
                    <span class="flex flex-col leading-tight">
                        <span class="text-[15px] font-bold tracking-tight text-ink">RAMP</span>
                        <span class="text-[11px] font-medium text-ink-muted">Rural Asset Management Platform</span>
                    </span>
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
        @if (session('notice'))
            <div class="mb-6 flex items-center gap-2.5 rounded-xl border border-hairline bg-surface px-4 py-3 text-sm text-ink-soft shadow-[var(--shadow-card)]">
                <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                {{ session('notice') }}
            </div>
        @endif

        {{ $slot }}
    </main>

    <footer class="mx-auto max-w-[1280px] px-4 pb-10 sm:px-6 lg:px-8">
        <div class="flex flex-col gap-3 border-t border-hairline/70 pt-5 text-[11px] font-medium text-ink-muted sm:flex-row sm:items-center sm:justify-between">
            <p>
                Developed by <a href="https://redmindtechnologies.com" target="_blank" class="font-bold hover:underline"><span style="color: #d93025;">R</span><span class="text-ink">ed</span><span style="color: #d93025;">M</span><span class="text-ink">ind Technologies</span></a>
            </p>
            <p>
                Support: <a href="mailto:support@redmindtechnologies.com" class="hover:text-brand transition">support@redmindtechnologies.com</a>
            </p>
            <p>
                &copy; {{ date('Y') }} ARD Agency &middot; Version 1.0.0
            </p>
        </div>
    </footer>
</body>
</html>
