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

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen text-ink"
      x-data="{
          collapsed: localStorage.getItem('ramp_collapsed') === '1',
          sidebarOpen: false,
          toggleCollapsed() { this.collapsed = !this.collapsed; localStorage.setItem('ramp_collapsed', this.collapsed ? '1' : '0'); }
      }">

    {{-- Mobile overlay --}}
    <div x-show="sidebarOpen" x-cloak x-transition.opacity @click="sidebarOpen = false"
         class="fixed inset-0 z-40 bg-ink/40 lg:hidden"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 left-0 z-50 border-r border-hairline bg-surface transition-all duration-200 ease-out"
           :class="[ collapsed ? 'w-16' : 'w-56', sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0' ]">
        <x-app.sidebar />
    </aside>

    {{-- Main column --}}
    <div class="flex min-h-screen flex-col transition-all duration-200 ease-out" :class="collapsed ? 'lg:pl-16' : 'lg:pl-56'">
        <x-app.topbar />

        <main class="relative mx-auto w-full flex-1 px-4 py-7 transition-all duration-200 ease-out sm:px-6 sm:py-8 lg:px-8"
              :class="collapsed ? 'max-w-[1600px]' : 'max-w-[1320px]'">
            {{-- Page-level Back button — top-right of every page except Home --}}
            @unless (request()->routeIs('home'))
                <button type="button" @click="window.history.back()"
                        class="absolute right-4 top-6 z-10 inline-flex h-9 items-center gap-1.5 rounded-lg border border-hairline bg-surface px-2.5 text-sm font-medium text-ink-soft shadow-[var(--shadow-card)] transition hover:border-brand/30 hover:text-brand sm:right-6 sm:top-7 lg:right-8"
                        aria-label="Go back" title="Back to previous page">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    <span class="hidden sm:inline">Back</span>
                </button>
            @endunless

            @if (session('notice'))
                <div class="mb-6 flex items-center gap-2.5 rounded-xl border border-hairline bg-surface px-4 py-3 text-sm text-ink-soft shadow-[var(--shadow-card)]">
                    <svg class="h-4 w-4 shrink-0 text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>
                    {{ session('notice') }}
                </div>
            @endif

            {{ $slot }}
        </main>

        {{-- Footer — sits at the bottom of the page in normal flow (not fixed) --}}
        <footer class="mx-auto w-full px-4 transition-all duration-200 ease-out sm:px-6 lg:px-8"
                :class="collapsed ? 'max-w-[1600px]' : 'max-w-[1320px]'">
            <x-app.footer />
        </footer>
    </div>
</body>
</html>
