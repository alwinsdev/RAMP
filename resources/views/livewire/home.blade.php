@php
    use App\Enums\LifecycleStatus;
    $total = max($summary->health->total(), 1);
@endphp

<div class="flex flex-col gap-9">
    {{-- Hero --}}
    <section class="flex flex-col gap-4">
        <span class="inline-flex w-fit items-center gap-2 rounded-full border border-hairline bg-surface px-3 py-1 text-xs font-semibold text-ink-soft shadow-[var(--shadow-card)]">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full opacity-60" style="background: var(--color-status-healthy);"></span>
                <span class="relative inline-flex h-2 w-2 rounded-full" style="background: var(--color-status-healthy);"></span>
            </span>
            Sprint 0 — Foundation Ready
        </span>

        <div class="flex flex-col gap-3">
            <h1 class="max-w-3xl text-3xl font-extrabold tracking-tight text-ink sm:text-[2.5rem] sm:leading-[1.1]">
                A single, trusted window into every rural public asset.
            </h1>
            <p class="max-w-2xl text-[15px] leading-relaxed text-ink-soft">
                The future-ready foundation is in place — a swappable data seam, the shared lifecycle engine,
                the aggregation service, and a premium component system. Every figure below is computed live
                through <code class="rounded bg-surface-soft px-1.5 py-0.5 text-[13px] font-medium text-ink ring-hairline">DashboardService</code>,
                proving the UI&nbsp;&rarr;&nbsp;Service&nbsp;&rarr;&nbsp;Provider&nbsp;&rarr;&nbsp;mock-data chain end&nbsp;to&nbsp;end.
            </p>
        </div>
    </section>

    {{-- KPI row — real figures from the aggregation service (no hard-coded counts, BR-DI-05) --}}
    <section aria-label="Headline metrics" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
        <x-kpi-card label="Total Assets" :value="$summary->totalAssets" sublabel="across the dataset"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6"/></svg>' />
        <x-kpi-card label="Asset Categories" :value="$summary->totalCategories" sublabel="classifications"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>' />
        <x-kpi-card label="Zones" :value="$summary->totalZones" sublabel="administrative"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>' />
        <x-kpi-card label="Panchayats" :value="$summary->totalPanchayats" sublabel="local units"
            icon='<svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M6 21V10m12 11V10M4 10l8-6 8 6M10 21v-5h4v5"/></svg>' />
    </section>

    {{-- Health distribution — proves the lifecycle engine + canonical status colors render --}}
    <section aria-label="Lifecycle health" class="flex flex-col gap-4">
        <div class="flex items-end justify-between">
            <div>
                <span class="eyebrow">Asset condition</span>
                <h2 class="text-lg font-bold tracking-tight text-ink">Lifecycle Health</h2>
            </div>
            <span class="text-sm text-ink-soft">{{ $summary->totalAssets }} assets monitored</span>
        </div>

        {{-- Stacked distribution bar --}}
        <div class="flex h-3 w-full overflow-hidden rounded-full bg-hairline-soft ring-hairline">
            @foreach (LifecycleStatus::displayOrder() as $status)
                @php $count = $summary->health->count($status); @endphp
                @if ($count > 0)
                    <div class="h-full transition-all" style="width: {{ $count / $total * 100 }}%; background-color: {{ $status->color() }};"
                         title="{{ $status->label() }}: {{ $count }}"></div>
                @endif
            @endforeach
        </div>

        <div class="grid grid-cols-2 gap-4 sm:grid-cols-4">
            @foreach (LifecycleStatus::displayOrder() as $status)
                <x-card class="flex flex-col gap-3">
                    <div class="flex items-center justify-between">
                        <x-status-badge :status="$status" />
                        <span class="text-2xl font-bold tabular-nums text-ink">{{ $summary->health->count($status) }}</span>
                    </div>
                    @if ($status->countsTowardHealth())
                        <span class="text-xs text-ink-muted">{{ $summary->health->percentage($status) }}% of rated assets</span>
                    @else
                        <span class="text-xs text-ink-muted">excluded from percentages</span>
                    @endif
                </x-card>
            @endforeach
        </div>
    </section>

    {{-- Foundation checklist --}}
    <section class="flex flex-col gap-4">
        <div>
            <span class="eyebrow">Under the hood</span>
            <h2 class="text-lg font-bold tracking-tight text-ink">What's wired</h2>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['Data seam', 'Mock JSON behind a contract — swap to Eloquent via one config flip, zero UI change.'],
                ['Lifecycle engine', 'One shared calculator; status computed at runtime, never stored. 17 boundary tests.'],
                ['Aggregation service', 'Every dashboard figure reconciles to the live dataset — no hard-coded counts.'],
                ['Search & filter', 'AND across filters, case-insensitive substring search — in the service layer.'],
                ['Reusable UI kit', 'Cards, badges, tables, breadcrumbs, empty states — built once, premium by default.'],
                ['Zero database', 'No migrations, no Eloquent, no auth — the POC reads and displays.'],
            ] as [$heading, $copy])
                <x-card class="flex gap-3.5">
                    <span class="mt-0.5 grid h-7 w-7 shrink-0 place-items-center rounded-lg text-white" style="background: var(--color-status-healthy);">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M5 13l4 4L19 7" /></svg>
                    </span>
                    <div class="flex flex-col gap-1">
                        <span class="font-semibold text-ink">{{ $heading }}</span>
                        <span class="text-sm leading-relaxed text-ink-soft">{{ $copy }}</span>
                    </div>
                </x-card>
            @endforeach
        </div>
    </section>

    <p class="text-sm text-ink-soft">
        <span class="font-semibold text-ink">Next:</span> Sprint 1 builds hierarchy navigation and the Asset List on this foundation.
    </p>
</div>
