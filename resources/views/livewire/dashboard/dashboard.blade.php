@php
    use App\Enums\LifecycleStatus;
    $statuses = LifecycleStatus::displayOrder();
    $series = array_map(fn ($s) => $summary->health->count($s), $statuses);
    $labels = array_map(fn ($s) => $s->label(), $statuses);
    $colors = array_map(fn ($s) => $s->color(), $statuses);
@endphp

<div class="flex flex-col gap-8">
    {{-- Title --}}
    <div class="flex flex-col gap-1">
        <span class="eyebrow">Command center</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Dashboard</h1>
        <p class="text-sm text-ink-soft">How many assets exist, where they are, and which need attention — every figure drills into the asset list.</p>
    </div>

    {{-- KPI row --}}
    <section aria-label="Headline metrics" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-kpi-card label="Total Assets" :value="$summary->totalAssets" sublabel="across the dataset" :href="route('assets')"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>' />
        <x-kpi-card label="Asset Categories" :value="$summary->totalCategories" sublabel="classifications"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>' />
        <x-kpi-card label="Zones" :value="$summary->totalZones" sublabel="administrative"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>' />
        <x-kpi-card label="Panchayats" :value="$summary->totalPanchayats" sublabel="local units"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M6 21V10m12 11V10M4 10l8-6 8 6M10 21v-5h4v5"/></svg>' />
    </section>

    {{-- Health summary: donut + status cards --}}
    <section aria-label="Lifecycle health" class="grid gap-4 lg:grid-cols-5">
        <x-card class="lg:col-span-2 flex flex-col">
            <span class="eyebrow">Asset health distribution</span>
            @if ($summary->totalAssets > 0)
                <div wire:ignore class="mt-2"
                     x-data="healthDonut({ series: @js($series), labels: @js($labels), colors: @js($colors), total: {{ $summary->totalAssets }} })">
                    <div x-ref="donut"></div>
                </div>
            @else
                <div class="mt-3"><x-empty-state title="No assets yet" message="Health distribution will appear once assets are recorded." /></div>
            @endif
        </x-card>

        <div class="lg:col-span-3 grid grid-cols-2 gap-4 sm:grid-cols-4 lg:grid-cols-2">
            @foreach ($statuses as $status)
                @php $count = $summary->health->count($status); @endphp
                <x-card :href="route('assets', ['status' => $status->value])" class="flex flex-col justify-between gap-3">
                    <div class="flex items-center justify-between">
                        <x-status-badge :status="$status" />
                        <svg class="h-4 w-4 text-ink-muted opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                    </div>
                    <div class="flex items-end justify-between">
                        <span class="text-3xl font-bold tabular-nums text-ink">{{ $count }}</span>
                        <span class="text-xs text-ink-muted">
                            @if ($status->countsTowardHealth())
                                {{ $summary->health->percentage($status) }}% of rated
                            @else
                                incomplete
                            @endif
                        </span>
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>

    {{-- Breakdowns --}}
    <section aria-label="Breakdowns" class="grid gap-4 lg:grid-cols-3">
        <x-breakdown-list title="Zone-wise" :rows="$summary->zoneBreakdown" />
        <x-breakdown-list title="Panchayat-wise" :rows="$summary->panchayatBreakdown" />
        <x-breakdown-list title="Category breakdown" :rows="$summary->categoryBreakdown" />
    </section>
</div>
