@php
    use App\Enums\UserRole;

    $user = auth()->user();
    $role = $user?->role;

    $isPanchayatOfficer = $role === UserRole::PanchayatOfficer;
    $isAdminOrDistrict = ! $isPanchayatOfficer;

    // Each item opens a flat index of every node in the user's scope; the cards then
    // drill down (Zone → Panchayats → Categories → Assets). A panchayat officer instead
    // lands directly on their own Panchayat Category Dashboard.
    $categoriesUrl = $isPanchayatOfficer && $user?->panchayatId
        ? route('categories', $user->panchayatId)
        : route('categories.index');

    // [label, icon, url, route-name match patterns, visible]
    $items = array_values(array_filter([
        ['Dashboard', 'dashboard', route('home'), ['home'], true],
        ['Districts', 'districts', route('districts'), ['districts'], $isAdminOrDistrict],
        ['Zones', 'zones', route('zones.index'), ['zones.index', 'zones'], $isAdminOrDistrict],
        ['Panchayats', 'panchayats', route('panchayats.index'), ['panchayats.index', 'panchayats'], $isAdminOrDistrict],
        ['Asset Categories', 'categories', $categoriesUrl, ['categories.index', 'categories'], true],
        ['Assets', 'assets', route('assets'), ['assets', 'assets.*'], true],
        ['Map View', 'map', route('map'), ['map'], true],
        ['Settings', 'settings', route('settings'), ['settings'], true],
    ], static fn (array $i): bool => $i[4]));
@endphp

<div class="flex h-full flex-col">
    {{-- Logo — a square brand emblem (always) + wordmark (expanded only) --}}
    <div class="flex h-16 shrink-0 items-center border-b border-hairline px-3" :class="collapsed ? 'justify-center' : 'px-4'">
        <a href="{{ route('home') }}" wire:navigate class="flex min-w-0 items-center gap-2.5">
            <span class="relative grid h-10 w-12 shrink-0 place-items-center overflow-hidden text-white" style="background-image: linear-gradient(140deg, var(--color-brand), var(--color-brand-strong));">
                <img src="{{ asset('images/ramp-logo.png') }}" alt="RAMP" class="h-10 w-12 object-cover"
                     onerror="this.style.display='none'; this.nextElementSibling.style.display='block';">
                <svg class="hidden h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>
            </span>
            <span x-show="!collapsed" x-cloak class="flex min-w-0 flex-col leading-tight whitespace-nowrap">
                <span class="text-sm font-bold tracking-tight text-ink">RAMP</span>
                <span class="text-[10px] font-medium text-ink-muted">Asset Platform</span>
            </span>
        </a>
    </div>

    {{-- Nav --}}
    <nav class="flex-1 space-y-1 overflow-y-auto overflow-x-hidden px-3 py-4">
        @foreach ($items as [$label, $icon, $url, $match])
            @php $active = request()->routeIs(...$match); @endphp
            <a href="{{ $url }}" wire:navigate
               @class([
                   'group/navitem relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition',
                   'bg-brand text-white shadow-sm' => $active,
                   'text-ink-soft hover:bg-surface-soft hover:text-ink' => ! $active,
               ])
               :class="{ 'justify-center': collapsed }"
               title="{{ $label }}"
               @click="sidebarOpen = false">
                <x-nav-icon :name="$icon" @class(['shrink-0', 'text-white' => $active]) />
                <span x-show="!collapsed" x-cloak class="whitespace-nowrap">{{ $label }}</span>
            </a>
        @endforeach
    </nav>
</div>
