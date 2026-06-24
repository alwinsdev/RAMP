@props([
    'markers' => [],
    'mapId' => 'map',
    'embedded' => false,
    'height' => '420px',
])

@php
    use App\Enums\LifecycleStatus;
@endphp

{{--
    Asset Intelligence Map (flagship visualization) — Leaflet + OpenStreetMap, no API
    key required. Colour-coded markers with clustering, a heatmap toggle, popups, and
    auto-fit to the (role-scoped, filtered) marker set. Reused full-screen and embedded
    on the dashboard.
--}}
<div class="relative overflow-hidden rounded-xl border border-hairline bg-surface shadow-[var(--shadow-card)]">
    {{-- Legend + heatmap toggle --}}
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-hairline px-4 py-2.5">
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-ink-soft">
            @foreach (LifecycleStatus::displayOrder() as $status)
                <span class="inline-flex items-center gap-1.5">
                    <span class="h-2.5 w-2.5 rounded-full" style="background: {{ $status->color() }};"></span>{{ $status->label() }}
                </span>
            @endforeach
            <span class="text-ink-muted">· {{ count($markers) }} mapped</span>
        </div>
        <button type="button" x-data @click="$dispatch('toggle-heatmap-{{ $mapId }}')"
                class="inline-flex items-center gap-1.5 rounded-lg border border-hairline bg-surface-soft px-2.5 py-1.5 text-xs font-semibold text-ink-soft transition hover:border-brand/30 hover:text-brand">
            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7z"/><circle cx="12" cy="9" r="2.5"/></svg>
            Heatmap
        </button>
    </div>

    {{-- Map (Livewire never re-renders this; updates arrive via the intelmap-data event) --}}
    <div wire:ignore
         x-data="assetIntelMap({ mapId: @js($mapId), embedded: {{ $embedded ? 'true' : 'false' }}, markers: @js($markers) })"
         @toggle-heatmap-{{ $mapId }}.window="toggleHeatmap()"
         @intelmap-data.window="onData($event.detail)">
        <div x-ref="map" x-show="!failed" style="height: {{ $height }};" class="w-full"></div>
        <div x-show="failed" x-cloak class="flex flex-col items-center justify-center gap-2 text-ink-soft" style="height: {{ $height }};">
            <svg class="h-8 w-8 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <p class="text-sm">Map could not be loaded.</p>
        </div>
    </div>
</div>
