@props([
    'asset',
    'height' => '220px',
    'interactive' => false,
])

@php
    // Status colour for the pin (computed lifecycle), falling back to brand blue.
    $pinColor = $asset->lifecycle?->status?->color() ?? '#1A73E8';
@endphp

{{--
    Reusable asset map (CR-07). Renders a Leaflet + OpenStreetMap marker at the
    asset's coordinates (no API key required), or a "location unavailable" panel when
    coordinates are missing. Reused by the Asset Information preview (read-only, 220px)
    and the full Location screen (interactive, taller).
--}}
@if ($asset->hasValidCoordinates())
    <div wire:ignore x-data="assetMap({ lat: {{ $asset->latitude }}, lng: {{ $asset->longitude }}, label: @js($asset->assetName), color: @js($pinColor), interactive: {{ $interactive ? 'true' : 'false' }} })"
         class="overflow-hidden rounded-xl border border-hairline bg-surface-soft">
        <div x-ref="map" x-show="!failed" style="height: {{ $height }};" class="w-full"></div>
        <div x-show="failed" x-cloak class="flex flex-col items-center justify-center gap-2 text-ink-soft" style="height: {{ $height }};">
            <svg class="h-7 w-7 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <p class="text-sm">Map could not be loaded.</p>
        </div>
    </div>
@else
    {{-- Missing/invalid coordinates (BR-LO-03) --}}
    <div class="flex flex-col items-center justify-center gap-2 rounded-xl border border-dashed border-hairline bg-surface-soft text-center" style="height: {{ $height }};">
        <span class="grid h-11 w-11 place-items-center rounded-2xl" style="background: color-mix(in srgb, #80868B 12%, white); color: #80868B;">
            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
        </span>
        <p class="font-semibold text-ink">Location unavailable</p>
        <p class="text-sm text-ink-soft">Coordinates are not recorded for this asset.</p>
    </div>
@endif
