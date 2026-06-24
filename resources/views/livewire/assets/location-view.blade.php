@php
    $lat = $asset->latitude;
    $lng = $asset->longitude;
    $hasCoords = $asset->hasValidCoordinates();
    $gmapsSearch = $hasCoords ? "https://www.google.com/maps/search/?api=1&query={$lat},{$lng}" : null;
    $gmapsDirections = $hasCoords ? "https://www.google.com/maps/dir/?api=1&destination={$lat},{$lng}" : null;
@endphp

<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <span class="eyebrow">Location · {{ $asset->assetName }}</span>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Location</h1>
        </div>
        <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to information
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-3">
        {{-- Large interactive map --}}
        <div class="lg:col-span-2">
            <x-map-embed :asset="$asset" height="clamp(300px, 60vh, 520px)" :interactive="true" />
        </div>

        {{-- Asset info panel --}}
        <x-card class="flex h-fit flex-col gap-4">
            <div class="flex items-center justify-between gap-3">
                <span class="eyebrow">Asset</span>
                <x-status-badge :status="$asset->lifecycle?->status" />
            </div>
            <div>
                <p class="font-bold text-ink">{{ $asset->assetName }}</p>
                <p class="font-mono text-xs text-ink-muted">{{ $asset->assetNumber }}</p>
            </div>

            <dl class="border-t border-hairline-soft pt-3">
                <x-detail-row label="Category" :value="$asset->categoryName" />
                <x-detail-row label="Panchayat" :value="$asset->panchayatName" />
                <x-detail-row label="Zone" :value="$asset->zoneName" />
                <x-detail-row label="District" :value="$asset->districtName" />
            </dl>

            <div class="border-t border-hairline-soft pt-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">Address</p>
                <p class="mt-1 text-sm text-ink">{{ $asset->address ?? 'Not recorded' }}</p>
                @if ($hasCoords)
                    <p class="mt-2 font-mono text-xs text-ink-soft">{{ $lat }}, {{ $lng }}</p>
                @endif
            </div>

            @if ($hasCoords)
                <div class="flex flex-col gap-2 border-t border-hairline-soft pt-3"
                     x-data="{ copied: false, copy() { navigator.clipboard.writeText('{{ $lat }}, {{ $lng }}'); this.copied = true; setTimeout(() => this.copied = false, 1500); } }">
                    <a href="{{ $gmapsDirections }}" target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-hover">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="m9 20-6-6 11-11 6 6-11 11z"/><path d="m14 9 1.5 1.5"/><path d="M3 14h6v6"/></svg>
                        Directions
                    </a>
                    <a href="{{ $gmapsSearch }}" target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center gap-2 rounded-lg border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-brand/30 hover:text-brand">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                        Open in Google Maps
                    </a>
                    <button type="button" @click="copy()"
                            class="inline-flex items-center justify-center gap-2 rounded-lg border border-hairline bg-surface px-4 py-2.5 text-sm font-semibold text-ink transition hover:border-brand/30 hover:text-brand">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/></svg>
                        <span x-show="!copied">Copy Coordinates</span>
                        <span x-show="copied" x-cloak class="text-status-healthy">Copied!</span>
                    </button>
                </div>
            @endif
        </x-card>
    </div>
</div>
