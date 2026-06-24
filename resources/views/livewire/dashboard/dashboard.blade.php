@php
    use App\Enums\LifecycleStatus;
    $statuses = LifecycleStatus::displayOrder();
    $series = array_map(fn ($s) => $summary->health->count($s), $statuses);
    $labels = array_map(fn ($s) => $s->label(), $statuses);
    $colors = array_map(fn ($s) => $s->color(), $statuses);

    // Hierarchy KPIs enter the hierarchy at Districts (CR-04: no card opens the Asset List directly).
    $districtsUrl = route('districts');
@endphp

<div class="flex flex-col gap-8">
    {{-- Title --}}
    <div class="flex flex-col gap-1">
        <span class="eyebrow">Monitoring overview</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Dashboard</h1>
        <p class="text-sm text-ink-soft">How many assets exist, where they are, and which need attention — start by choosing a district.</p>
    </div>

    {{-- 1. KPI row (7) — hierarchy KPIs drill to Districts; health KPIs drill to the filtered Asset List --}}
    <section aria-label="Key metrics" class="grid grid-cols-2 gap-4 md:grid-cols-4 xl:grid-cols-7">
        <x-kpi-card label="Total Assets" :value="$summary->totalAssets" sublabel="all assets" :href="$districtsUrl" accent="#1A73E8"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3.3 7 12 12l8.7-5M12 22V12"/><path d="m3.3 7 8.7-5 8.7 5v10L12 22 3.3 17z"/></svg>' />
        <x-kpi-card label="Districts" :value="$summary->totalDistricts" :href="$districtsUrl" accent="#4F46E5"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l7-4 7 4v14"/><path d="M9 21v-5h6v5"/></svg>' />
        <x-kpi-card label="Zones" :value="$summary->totalZones" :href="$districtsUrl" accent="#0D9488"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>' />
        <x-kpi-card label="Panchayats" :value="$summary->totalPanchayats" :href="$districtsUrl" accent="#7C3AED"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M6 21V10m12 11V10M4 10l8-6 8 6M10 21v-5h4v5"/></svg>' />

        @foreach ([LifecycleStatus::Healthy, LifecycleStatus::NearExpiry, LifecycleStatus::Expired] as $st)
            <x-kpi-card :label="$st->label()" :value="$summary->health->count($st)" :accent="$st->color()"
                sublabel="{{ $summary->health->percentage($st) }}% of rated"
                :href="route('assets', ['status' => $st->value])"
                icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6"/></svg>' />
        @endforeach
    </section>

    {{-- 2. District cards — primary hierarchy drill-down --}}
    <section aria-label="Districts" class="flex flex-col gap-4">
        <div class="flex items-end justify-between">
            <div>
                <span class="eyebrow">Browse by district</span>
                <h2 class="text-lg font-bold tracking-tight text-ink">Districts</h2>
            </div>
            <a href="{{ route('districts') }}" wire:navigate class="text-sm font-semibold text-brand hover:text-brand-hover">View all →</a>
        </div>
        @if (count($summary->districtCards) === 0)
            <x-empty-state title="No districts in your view" message="No districts are available for your account." />
        @else
            <div class="grid gap-4 lg:grid-cols-2">
                @foreach ($summary->districtCards as $district)
                    <x-district-card :district="$district" />
                @endforeach
            </div>
        @endif
    </section>

    {{-- 3 + 4. Asset distribution + Lifecycle health --}}
    <section class="grid gap-4 lg:grid-cols-2">
        {{-- Asset distribution by category (informational) --}}
        <x-card class="flex flex-col">
            <span class="eyebrow">Asset distribution</span>
            <h2 class="text-lg font-bold tracking-tight text-ink">By category</h2>
            <div class="mt-4 flex flex-col gap-2.5">
                @foreach ($summary->categoryDistribution as $row)
                    <div class="flex items-center gap-3">
                        <span class="grid h-7 w-7 shrink-0 place-items-center rounded-lg text-brand" style="background: var(--color-brand-tint);">
                            <x-category-icon :id="$row->id" class="h-4 w-4" />
                        </span>
                        <span class="w-40 shrink-0 truncate text-sm text-ink">{{ $row->name }}</span>
                        <span class="h-2 flex-1 overflow-hidden rounded-full bg-hairline-soft">
                            <span class="block h-full rounded-full bg-brand" style="width: {{ $row->count / $summary->maxCategoryCount() * 100 }}%;"></span>
                        </span>
                        <span class="w-8 shrink-0 text-right text-sm font-semibold tabular-nums text-ink">{{ $row->count }}</span>
                    </div>
                @endforeach
            </div>
        </x-card>

        {{-- Lifecycle health --}}
        <x-card class="flex flex-col">
            <span class="eyebrow">Asset condition</span>
            <h2 class="text-lg font-bold tracking-tight text-ink">Lifecycle health</h2>
            @if ($summary->totalAssets > 0)
                <div wire:ignore class="mt-1"
                     x-data="healthDonut({ series: @js($series), labels: @js($labels), colors: @js($colors), total: {{ $summary->totalAssets }} })">
                    <div x-ref="donut"></div>
                </div>
                <div class="mt-2 grid grid-cols-2 gap-2">
                    @foreach ($statuses as $status)
                        <a href="{{ route('assets', ['status' => $status->value]) }}" wire:navigate
                           class="flex items-center justify-between rounded-lg border border-hairline-soft px-3 py-2 transition hover:bg-surface-soft">
                            <x-status-badge :status="$status" />
                            <span class="text-sm font-bold tabular-nums text-ink">{{ $summary->health->count($status) }}</span>
                        </a>
                    @endforeach
                </div>
            @else
                <div class="mt-3"><x-empty-state title="No assets yet" message="Health will appear once assets are recorded." /></div>
            @endif
        </x-card>
    </section>

    {{-- 5. Recent assets --}}
    <section aria-label="Recent assets" class="flex flex-col gap-4">
        <div>
            <span class="eyebrow">Latest additions</span>
            <h2 class="text-lg font-bold tracking-tight text-ink">Recent assets</h2>
        </div>
        @if (count($summary->recentAssets) === 0)
            <x-empty-state title="No assets yet" message="Recently added assets will appear here." />
        @else
            <div class="overflow-hidden rounded-xl border border-hairline bg-surface shadow-[var(--shadow-card)]">
                <ul class="divide-y divide-hairline-soft">
                    @foreach ($summary->recentAssets as $asset)
                        <li>
                            <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate
                               class="group flex items-center gap-3 px-4 py-3 transition hover:bg-surface-soft">
                                <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg text-brand" style="background: var(--color-brand-tint);">
                                    <x-category-icon :id="$asset->categoryId" class="h-4 w-4" />
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-semibold text-ink">{{ $asset->assetName }}</p>
                                    <p class="truncate text-xs text-ink-muted">{{ $asset->categoryName }} · {{ $asset->panchayatName }}</p>
                                </div>
                                <span class="hidden font-mono text-xs text-ink-muted sm:block">{{ $asset->assetNumber }}</span>
                                <x-status-badge :status="$asset->lifecycle?->status" />
                                <svg class="h-4 w-4 shrink-0 text-ink-muted transition group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </section>
</div>
