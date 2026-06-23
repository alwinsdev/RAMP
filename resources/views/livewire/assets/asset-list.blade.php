<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-4">
        <div class="flex flex-col gap-1">
            <span class="eyebrow">Assets</span>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Asset List</h1>
        </div>

        {{-- Toolbar: search + active filter chips + reset --}}
        <div class="flex flex-col gap-3 rounded-xl border border-hairline bg-surface p-4 shadow-[var(--shadow-card)]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <label class="relative flex-1 sm:max-w-md">
                    <span class="sr-only">Search assets</span>
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                    <input
                        type="search"
                        wire:model.live.debounce.300ms="q"
                        placeholder="Search by name or asset number…"
                        class="w-full rounded-lg border border-hairline bg-surface-soft py-2.5 pl-9 pr-3 text-sm text-ink placeholder:text-ink-muted focus:border-brand focus:bg-surface focus:outline-none"
                    >
                </label>

                <div class="flex items-center gap-3 text-sm text-ink-soft">
                    <span wire:loading.remove>Showing <span class="font-semibold text-ink tabular-nums">{{ $resultCount }}</span> {{ \Illuminate\Support\Str::plural('asset', $resultCount) }}</span>
                    <span wire:loading class="text-ink-muted">Searching…</span>
                </div>
            </div>

            {{-- Filter selects (AND across filters; panchayat constrained by zone, type by category) --}}
            @php $selectClass = 'w-full rounded-lg border border-hairline bg-surface-soft px-3 py-2 text-sm text-ink focus:border-brand focus:bg-surface focus:outline-none'; @endphp
            <div class="grid grid-cols-2 gap-2 border-t border-hairline-soft pt-3 sm:grid-cols-3 lg:grid-cols-5">
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
                <select wire:model.live="assetType" class="{{ $selectClass }}" aria-label="Filter by asset type">
                    <option value="">All types</option>
                    @foreach ($filterOptions['types'] as $type)
                        <option value="{{ $type }}">{{ $type }}</option>
                    @endforeach
                </select>
                <select wire:model.live="status" class="{{ $selectClass }}" aria-label="Filter by status">
                    <option value="">All statuses</option>
                    @foreach ($filterOptions['statuses'] as $statusOption)
                        <option value="{{ $statusOption }}">{{ $statusOption }}</option>
                    @endforeach
                </select>
            </div>

            @if (count($activeFilters) > 0)
                <div class="flex flex-wrap items-center gap-2 border-t border-hairline-soft pt-3">
                    <span class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Filters</span>
                    @foreach ($activeFilters as $chip)
                        <span class="inline-flex items-center gap-1.5 rounded-full border border-hairline bg-surface-soft py-1 pl-2.5 pr-1.5 text-xs font-medium text-ink">
                            <span class="text-ink-muted">{{ $chip['label'] }}:</span> {{ $chip['value'] }}
                            <button type="button" wire:click="removeFilter('{{ $chip['key'] }}')" class="grid h-4 w-4 place-items-center rounded-full text-ink-muted hover:bg-hairline hover:text-ink" aria-label="Remove {{ $chip['label'] }} filter">
                                <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                            </button>
                        </span>
                    @endforeach
                    <button type="button" wire:click="resetFilters" class="ml-1 text-xs font-semibold text-brand hover:text-brand-hover">Reset all</button>
                </div>
            @endif
        </div>
    </div>

    @if ($resultCount === 0)
        <x-empty-state title="No assets match your search and filters" message="Try a different search term or clear the active filters.">
            <x-slot:action>
                <button type="button" wire:click="resetFilters" class="rounded-lg bg-brand px-4 py-2 text-sm font-semibold text-white transition hover:bg-brand-hover">Reset filters</button>
            </x-slot:action>
        </x-empty-state>
    @else
        <x-data-table :headers="[
            ['label' => 'Asset No.', 'align' => 'left'],
            ['label' => 'Asset Name', 'align' => 'left'],
            ['label' => 'Category', 'align' => 'left'],
            ['label' => 'Type', 'align' => 'left'],
            ['label' => 'Panchayat', 'align' => 'left'],
            ['label' => 'Status', 'align' => 'center'],
            ['label' => 'Remaining Life', 'align' => 'right'],
        ]">
            @foreach ($assets as $asset)
                <tr class="cursor-pointer transition hover:bg-surface-soft" onclick="window.Livewire.navigate('{{ route('assets.show', ['asset' => $asset->id]) }}')">
                    <td class="whitespace-nowrap px-4 py-3 font-mono text-xs text-ink-soft">{{ $asset->assetNumber }}</td>
                    <td class="px-4 py-3">
                        <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate class="font-semibold text-ink hover:text-brand" onclick="event.stopPropagation()">{{ $asset->assetName }}</a>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center gap-1.5 text-ink-soft"><x-category-icon :id="$asset->categoryId" class="h-4 w-4 text-ink-muted" /> {{ $asset->categoryName }}</span>
                    </td>
                    <td class="px-4 py-3 text-ink-soft">{{ $asset->assetType }}</td>
                    <td class="px-4 py-3 text-ink-soft">{{ $asset->panchayatName }}</td>
                    <td class="px-4 py-3 text-center"><x-status-badge :status="$asset->lifecycle?->status" /></td>
                    <td class="px-4 py-3 text-right tabular-nums text-ink-soft">
                        {{ is_null($asset->lifecycle?->remainingLife) ? '—' : $asset->lifecycle->remainingLife.' yr' }}
                    </td>
                </tr>
            @endforeach

            <x-slot:cards>
                @foreach ($assets as $asset)
                    <x-asset-card :asset="$asset" />
                @endforeach
            </x-slot:cards>
        </x-data-table>
    @endif
</div>
