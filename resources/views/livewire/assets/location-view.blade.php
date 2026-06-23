<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <span class="eyebrow">Location · {{ $asset->assetName }}</span>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Location</h1>
        </div>
        <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to detail
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Map (primary) --}}
        <div class="lg:col-span-2">
            @if ($asset->hasValidCoordinates() && $mapsKey !== '')
                <div x-data="assetMap({ lat: {{ $asset->latitude }}, lng: {{ $asset->longitude }}, key: @js($mapsKey), label: @js($asset->assetName) })"
                     class="overflow-hidden rounded-xl border border-hairline bg-surface shadow-[var(--shadow-card)]">
                    <div x-ref="map" x-show="!failed" class="h-[300px] w-full sm:h-[440px]"></div>
                    <div x-show="failed" x-cloak class="flex h-[300px] flex-col items-center justify-center gap-2 text-ink-soft sm:h-[440px]">
                        <svg class="h-8 w-8 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                        <p class="text-sm">Map could not be loaded.</p>
                    </div>
                </div>
            @elseif ($asset->hasValidCoordinates())
                {{-- Valid coordinates but no API key configured: graceful coordinate preview --}}
                <div class="flex h-[300px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-hairline bg-surface text-center shadow-[var(--shadow-card)] sm:h-[440px]">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl text-brand" style="background: var(--color-brand-tint);">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                    </span>
                    <p class="font-mono text-sm text-ink">{{ $asset->latitude }}, {{ $asset->longitude }}</p>
                    <p class="max-w-xs text-xs text-ink-muted">Add a <span class="font-semibold">GOOGLE_MAPS_API_KEY</span> to render the interactive map. Coordinates are valid and ready.</p>
                </div>
            @else
                {{-- Missing/invalid coordinates (BR-LO-03) --}}
                <div class="flex h-[300px] flex-col items-center justify-center gap-3 rounded-xl border border-dashed border-hairline bg-surface text-center shadow-[var(--shadow-card)] sm:h-[440px]">
                    <span class="grid h-14 w-14 place-items-center rounded-2xl text-status-unknown" style="background: color-mix(in srgb, #80868B 12%, white);">
                        <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                    </span>
                    <p class="font-semibold text-ink">Location unavailable</p>
                    <p class="max-w-xs text-sm text-ink-soft">Location coordinates are not recorded for this asset.</p>
                </div>
            @endif
        </div>

        {{-- Address + coordinate readout --}}
        <x-card class="h-fit">
            <span class="eyebrow">Address &amp; coordinates</span>
            <div class="mt-3 flex flex-col gap-3">
                @if ($asset->address)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Address</p>
                        <p class="mt-1 text-sm text-ink">{{ $asset->address }}</p>
                    </div>
                @else
                    <p class="text-sm text-ink-soft">No address recorded.</p>
                @endif

                @if ($asset->hasValidCoordinates())
                    <dl class="border-t border-hairline-soft pt-3">
                        <x-detail-row label="Latitude" :value="(string) $asset->latitude" mono />
                        <x-detail-row label="Longitude" :value="(string) $asset->longitude" mono />
                    </dl>
                @endif
            </div>
        </x-card>
    </div>
</div>
