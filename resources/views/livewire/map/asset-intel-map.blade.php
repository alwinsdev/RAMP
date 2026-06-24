@php $selectClass = 'w-full rounded-lg border border-hairline bg-surface-soft px-3 py-2 text-sm text-ink focus:border-brand focus:bg-surface focus:outline-none'; @endphp

<div class="flex flex-col gap-5">
    <div class="flex flex-col gap-1">
        <span class="eyebrow">Government asset intelligence</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Asset Intelligence Map</h1>
        <p class="text-sm text-ink-soft">Every asset, colour-coded by health. Filter by area or category and the map focuses automatically. Switch to heatmap to see concentration.</p>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col gap-3 rounded-xl border border-hairline bg-surface p-4 shadow-[var(--shadow-card)]">
        <div class="grid grid-cols-2 gap-2 sm:grid-cols-3 lg:grid-cols-5">
            <select wire:model.live="districtId" class="{{ $selectClass }}" aria-label="Filter by district">
                <option value="">All districts</option>
                @foreach ($filterOptions['districts'] as $district)
                    <option value="{{ $district->id }}">{{ $district->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="zoneId" class="{{ $selectClass }}" aria-label="Filter by zone">
                <option value="">All zones</option>
                @foreach ($filterOptions['zones'] as $zone)
                    <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="panchayatId" class="{{ $selectClass }}" aria-label="Filter by panchayat">
                <option value="">All panchayats</option>
                @foreach ($filterOptions['panchayats'] as $panchayat)
                    <option value="{{ $panchayat->id }}">{{ $panchayat->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="categoryId" class="{{ $selectClass }}" aria-label="Filter by category">
                <option value="">All categories</option>
                @foreach ($filterOptions['categories'] as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="status" class="{{ $selectClass }}" aria-label="Filter by health status">
                <option value="">All health statuses</option>
                @foreach ($filterOptions['statuses'] as $statusOption)
                    <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-center justify-between border-t border-hairline-soft pt-3">
            <span class="text-sm text-ink-soft"><span class="font-semibold text-ink tabular-nums">{{ $mappedCount }}</span> assets on the map</span>
            <button type="button" wire:click="resetFilters" class="text-xs font-semibold text-brand hover:text-brand-hover">Reset filters</button>
        </div>
    </div>

    <x-asset-map :markers="$markers" :map-id="$this->getId()" height="clamp(440px, 66vh, 760px)" />
</div>
