@php
    use App\Enums\LifecycleStatus;
    $lc = $asset->lifecycle;
    $isUnknown = ! $lc || $lc->status === LifecycleStatus::Unknown;
@endphp

<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    {{-- Header: name · number · status + quick actions --}}
    <div class="flex flex-col gap-5 rounded-xl border border-hairline bg-surface p-5 shadow-[var(--shadow-card)] sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex items-start gap-4">
                <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl text-brand" style="background: var(--color-brand-tint);">
                    <x-category-icon :id="$asset->categoryId" class="h-6 w-6" />
                </span>
                <div>
                    <h1 class="text-xl font-bold tracking-tight text-ink sm:text-2xl">{{ $asset->assetName }}</h1>
                    <p class="mt-1 flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-ink-soft">
                        <span class="font-mono text-ink-muted">{{ $asset->assetNumber }}</span>
                        <span aria-hidden="true">·</span>
                        <span>{{ $asset->categoryName }}</span>
                    </p>
                </div>
            </div>
            <x-status-badge :status="$lc?->status" size="lg" />
        </div>

        <div class="flex flex-wrap gap-2 border-t border-hairline-soft pt-4">
            <a href="{{ route('assets.location', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-hairline bg-surface-soft px-3.5 py-2 text-sm font-semibold text-ink transition hover:border-brand/30 hover:text-brand">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s-7-5.5-7-11a7 7 0 0 1 14 0c0 5.5-7 11-7 11z"/><circle cx="12" cy="10" r="2.5"/></svg>
                View on Map
            </a>
            <a href="{{ route('assets.photos', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-hairline bg-surface-soft px-3.5 py-2 text-sm font-semibold text-ink transition hover:border-brand/30 hover:text-brand">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                View Photos
            </a>
            <a href="{{ route('assets.lifecycle', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-2 rounded-lg border border-hairline bg-surface-soft px-3.5 py-2 text-sm font-semibold text-ink transition hover:border-brand/30 hover:text-brand">
                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                Asset Health
            </a>
        </div>
    </div>

    {{-- Asset information + Administrative --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <x-card>
            <span class="eyebrow">Asset information</span>
            <dl class="mt-3">
                <x-detail-row label="Asset Name" :value="$asset->assetName" />
                <x-detail-row label="Asset Number" :value="$asset->assetNumber" mono />
                <x-detail-row label="Category" :value="$asset->categoryName" />
                <x-detail-row label="Asset Type" :value="$asset->assetType" />
            </dl>
        </x-card>

        <x-card>
            <span class="eyebrow">Administrative</span>
            <dl class="mt-3">
                <x-detail-row label="District" :value="$asset->districtName" />
                <x-detail-row label="Zone" :value="$asset->zoneName" />
                <x-detail-row label="Panchayat" :value="$asset->panchayatName" />
            </dl>
        </x-card>
    </div>

    {{-- Asset health + Location --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <x-card class="flex flex-col">
            <div class="flex items-center justify-between">
                <span class="eyebrow">Asset health</span>
                <a href="{{ route('assets.lifecycle', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover">
                    Details
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <dl class="mt-3">
                <x-detail-row label="Construction Year" :value="$asset->constructionYear ? (string) $asset->constructionYear : '—'" />
                <x-detail-row label="Asset Age" :value="is_null($lc?->currentAge) ? '—' : $lc->currentAge.' yr'" />
                <x-detail-row label="Remaining Life" :value="is_null($lc?->remainingLife) ? '—' : $lc->remainingLife.' yr'" />
                <div class="flex items-center justify-between gap-4 py-2.5">
                    <dt class="text-sm text-ink-soft">Health Status</dt>
                    <dd><x-status-badge :status="$lc?->status" /></dd>
                </div>
            </dl>
            <div class="mt-3 border-t border-hairline-soft pt-4">
                <x-lifecycle-progress :asset="$asset" />
            </div>
        </x-card>

        <x-card class="flex flex-col">
            <div class="flex items-center justify-between">
                <span class="eyebrow">Location</span>
                <a href="{{ route('assets.location', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover">
                    View on Map
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            @if ($asset->address)
                <p class="mt-3 text-sm text-ink">{{ $asset->address }}</p>
            @endif

            <div class="mt-3">
                <x-map-embed :asset="$asset" height="220px" />
            </div>

            @if ($asset->hasValidCoordinates())
                <dl class="mt-3">
                    <x-detail-row label="Latitude" :value="(string) $asset->latitude" mono />
                    <x-detail-row label="Longitude" :value="(string) $asset->longitude" mono />
                </dl>
            @endif
        </x-card>
    </div>

    {{-- Photos --}}
    <x-card>
        <div class="flex items-center justify-between">
            <span class="eyebrow">Photos</span>
            @if ($asset->hasPhotos())
                <a href="{{ route('assets.photos', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover">
                    View gallery
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            @endif
        </div>
        <div class="mt-3">
            <x-photo-grid :photos="$asset->photos" :asset-name="$asset->assetName" />
        </div>
    </x-card>
</div>
