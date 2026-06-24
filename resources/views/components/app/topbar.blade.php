@php $user = auth()->user(); @endphp

<header class="sticky top-0 z-30 flex h-16 items-center gap-3 border-b border-hairline bg-surface/85 px-4 backdrop-blur-md sm:px-6" style="box-shadow: var(--shadow-header);">
    {{-- Sidebar toggle: drawer on mobile, collapse on desktop --}}
    <button type="button"
            @click="window.innerWidth < 1024 ? (sidebarOpen = !sidebarOpen) : toggleCollapsed()"
            class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-ink-soft transition hover:bg-surface-soft hover:text-ink"
            aria-label="Toggle navigation">
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
    </button>

    {{-- Page wordmark (the logo lives in the sidebar; the header stays clean) --}}
    <span class="hidden flex-col leading-tight sm:flex">
        <span class="text-sm font-bold tracking-tight text-ink">Rural Asset Management Platform</span>
        <span class="text-[10px] font-medium text-ink-muted">Government of Tamil Nadu · Proof of Concept</span>
    </span>

    <div class="flex-1"></div>

    {{-- User menu --}}
    <div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
        <button type="button" @click="open = !open"
                class="flex items-center gap-2.5 rounded-lg py-1 pl-1 pr-2 transition hover:bg-surface-soft">
            <span class="grid h-9 w-9 place-items-center rounded-full bg-brand-tint text-sm font-bold text-brand">{{ $user?->initials() }}</span>
            <span class="hidden flex-col items-start leading-tight sm:flex">
                <span class="text-sm font-semibold text-ink">{{ $user?->name }}</span>
                <span class="text-[11px] text-ink-muted">{{ $user?->role->label() }}</span>
            </span>
            <svg class="hidden h-4 w-4 text-ink-muted sm:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
        </button>

        <div x-show="open" x-cloak x-transition.origin.top.right @click.outside="open = false"
             class="absolute right-0 mt-2 w-60 overflow-hidden rounded-xl border border-hairline bg-surface shadow-[var(--shadow-raised)]">
            <div class="border-b border-hairline-soft px-4 py-3">
                <p class="text-sm font-semibold text-ink">{{ $user?->name }}</p>
                <p class="truncate text-xs text-ink-muted">{{ $user?->email }}</p>
                <span class="mt-1.5 inline-flex items-center gap-1.5 rounded-full bg-brand-tint px-2 py-0.5 text-[11px] font-semibold text-brand">{{ $user?->role->label() }}</span>
            </div>
            <a href="{{ route('settings') }}" wire:navigate class="flex items-center gap-2.5 px-4 py-2.5 text-sm text-ink-soft transition hover:bg-surface-soft hover:text-ink">
                <x-nav-icon name="settings" class="h-4 w-4" /> Settings
            </a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2.5 border-t border-hairline-soft px-4 py-2.5 text-sm font-medium text-status-expired transition hover:bg-status-expired/5">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
                    Sign out
                </button>
            </form>
        </div>
    </div>
</header>
