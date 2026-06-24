@php
    use App\Enums\LifecycleStatus;
    $score = $health->healthScore();
    // Score colour: green if strong, amber if moderate, red if weak.
    $scoreColor = $score >= 70 ? LifecycleStatus::Healthy->color() : ($score >= 40 ? LifecycleStatus::NearExpiry->color() : LifecycleStatus::Expired->color());
@endphp

<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-1">
        <span class="eyebrow">Panchayat asset categories</span>
        <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">{{ $panchayat->name }}</h1>
        <p class="text-sm text-ink-soft">Select a category to view and monitor its assets in this panchayat.</p>
    </div>

    {{-- Panchayat at-a-glance roll-up strip --}}
    <div class="flex flex-col gap-4 rounded-2xl border border-hairline bg-surface p-5 shadow-[var(--shadow-card)] sm:flex-row sm:items-center sm:justify-between sm:p-6">
        <div class="flex flex-wrap items-center gap-x-6 gap-y-3">
            <div>
                <p class="text-3xl font-extrabold leading-none tabular-nums text-ink">{{ $health->total() }}</p>
                <p class="mt-1 text-xs font-medium text-ink-muted">Total assets</p>
            </div>
            <div class="hidden h-10 w-px bg-hairline sm:block"></div>
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2">
                <span class="inline-flex items-center gap-2 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Healthy->color() }};"></span>
                    <span class="font-bold tabular-nums text-ink">{{ $health->healthy }}</span> <span class="text-ink-soft">Healthy</span>
                </span>
                <span class="inline-flex items-center gap-2 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::NearExpiry->color() }};"></span>
                    <span class="font-bold tabular-nums text-ink">{{ $health->nearExpiry }}</span> <span class="text-ink-soft">Near Expiry</span>
                </span>
                <span class="inline-flex items-center gap-2 text-sm">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Expired->color() }};"></span>
                    <span class="font-bold tabular-nums text-ink">{{ $health->expired }}</span> <span class="text-ink-soft">Expired</span>
                </span>
                @if ($health->unknown > 0)
                    <span class="inline-flex items-center gap-2 text-sm">
                        <span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Unknown->color() }};"></span>
                        <span class="font-bold tabular-nums text-ink">{{ $health->unknown }}</span> <span class="text-ink-soft">Unknown</span>
                    </span>
                @endif
            </div>
        </div>

        {{-- Asset Health Score --}}
        <div class="flex items-center gap-3 rounded-xl px-4 py-3" style="background: color-mix(in srgb, {{ $scoreColor }} 10%, white);">
            <div class="text-right">
                <p class="text-2xl font-extrabold leading-none tabular-nums" style="color: {{ $scoreColor }};">{{ $score }}%</p>
                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wide text-ink-muted">Health score</p>
            </div>
        </div>
    </div>

    {{-- 10 category cards · responsive 1 / 2 / 3 columns --}}
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($summaries as $summary)
            <x-category-card :summary="$summary" :panchayat-id="$panchayatId" />
        @endforeach
    </div>
</div>
