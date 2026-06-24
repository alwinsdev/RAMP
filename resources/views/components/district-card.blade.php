@props(['district'])

@php
    use App\Enums\LifecycleStatus;
    $total = max($district->health->total(), 1);
@endphp

{{-- District summary card (CR-09) — drills into the district's zones (CR-04). --}}
<x-card :href="route('zones', ['district' => $district->id])" class="flex flex-col gap-4">
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-brand" style="background: var(--color-brand-tint);">
                <x-nav-icon name="districts" class="h-6 w-6" />
            </span>
            <div>
                <p class="text-base font-bold text-ink">{{ $district->name }}</p>
                <p class="text-xs text-ink-muted">{{ $district->zoneCount }} {{ \Illuminate\Support\Str::plural('zone', $district->zoneCount) }} · {{ $district->panchayatCount }} {{ \Illuminate\Support\Str::plural('panchayat', $district->panchayatCount) }}</p>
            </div>
        </div>
        <div class="text-right">
            <p class="text-2xl font-extrabold leading-none tabular-nums text-ink">{{ $district->assetCount }}</p>
            <p class="mt-1 text-[11px] text-ink-muted">assets</p>
        </div>
    </div>

    {{-- Mini health distribution bar --}}
    <div>
        <div class="flex h-2 w-full overflow-hidden rounded-full bg-hairline-soft ring-hairline">
            @foreach (LifecycleStatus::displayOrder() as $status)
                @php $c = $district->health->count($status); @endphp
                @if ($c > 0)
                    <div class="h-full" style="width: {{ $c / $total * 100 }}%; background-color: {{ $status->color() }};" title="{{ $status->label() }}: {{ $c }}"></div>
                @endif
            @endforeach
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-[11px] text-ink-soft">
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::Healthy->color() }}"></span>{{ $district->health->healthy }} Healthy</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::NearExpiry->color() }}"></span>{{ $district->health->nearExpiry }} Near</span>
            <span class="inline-flex items-center gap-1"><span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::Expired->color() }}"></span>{{ $district->health->expired }} Expired</span>
        </div>
    </div>

    <span class="inline-flex items-center gap-1 text-sm font-semibold text-brand">
        View zones
        <svg class="h-4 w-4 transition group-hover:translate-x-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
    </span>
</x-card>
