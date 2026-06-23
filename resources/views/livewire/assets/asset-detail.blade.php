@php
    use App\Enums\LifecycleStatus;
    $lc = $asset->lifecycle;
    $isUnknown = ! $lc || $lc->status === LifecycleStatus::Unknown;
@endphp

<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    {{-- Header + status banner --}}
    <div class="flex flex-col gap-4 rounded-xl border border-hairline bg-surface p-5 shadow-[var(--shadow-card)] sm:flex-row sm:items-center sm:justify-between sm:p-6">
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

    {{-- Administrative + Asset information --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <x-card>
            <span class="eyebrow">Administrative information</span>
            <dl class="mt-3">
                <x-detail-row label="District" :value="$asset->districtName" />
                <x-detail-row label="Zone" :value="$asset->zoneName" />
                <x-detail-row label="Panchayat" :value="$asset->panchayatName" />
            </dl>
        </x-card>

        <x-card>
            <span class="eyebrow">Asset information</span>
            <dl class="mt-3">
                <x-detail-row label="Asset Number" :value="$asset->assetNumber" mono />
                <x-detail-row label="Asset Name" :value="$asset->assetName" />
                <x-detail-row label="Category" :value="$asset->categoryName" />
                <x-detail-row label="Asset Type" :value="$asset->assetType" />
            </dl>
        </x-card>
    </div>

    {{-- Location + Lifecycle --}}
    <div class="grid gap-4 lg:grid-cols-2">
        <x-card>
            <div class="flex items-center justify-between">
                <span class="eyebrow">Location</span>
                <a href="{{ route('assets.location', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover">
                    View on map
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            @if ($asset->address)
                <p class="mt-3 text-sm text-ink">{{ $asset->address }}</p>
            @endif
            @if ($asset->hasValidCoordinates())
                <dl class="mt-2">
                    <x-detail-row label="Latitude" :value="(string) $asset->latitude" mono />
                    <x-detail-row label="Longitude" :value="(string) $asset->longitude" mono />
                </dl>
            @else
                <div class="mt-3 flex items-center gap-2 rounded-lg bg-surface-soft px-3 py-2.5 text-sm text-ink-soft ring-hairline">
                    <svg class="h-4 w-4 text-ink-muted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 9v4M12 17h.01M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0z"/></svg>
                    Location coordinates unavailable for this asset.
                </div>
            @endif
        </x-card>

        <x-card>
            <div class="flex items-center justify-between">
                <span class="eyebrow">Lifecycle</span>
                <a href="{{ route('assets.lifecycle', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex items-center gap-1 text-xs font-semibold text-brand hover:text-brand-hover">
                    Lifecycle detail
                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </a>
            </div>
            <dl class="mt-3">
                <x-detail-row label="Construction Year" :value="$asset->constructionYear ? (string) $asset->constructionYear : '—'" />
                <x-detail-row label="Expected Life" :value="$asset->expectedLife ? $asset->expectedLife.' yr' : '—'" />
                <x-detail-row label="Current Age" :value="is_null($lc?->currentAge) ? '—' : $lc->currentAge.' yr'" />
                <x-detail-row label="Remaining Life" :value="is_null($lc?->remainingLife) ? '—' : $lc->remainingLife.' yr'" />
                <div class="flex items-center justify-between gap-4 pt-3">
                    <dt class="text-sm text-ink-soft">Status</dt>
                    <dd><x-status-badge :status="$lc?->status" /></dd>
                </div>
            </dl>
            @if ($isUnknown)
                <p class="mt-3 text-xs text-ink-muted">Lifecycle inputs are missing or invalid, so the status is Unknown and excluded from health percentages.</p>
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
        @if ($asset->hasPhotos())
            <div class="mt-3 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach ($asset->photos as $photo)
                    <figure class="overflow-hidden rounded-lg border border-hairline bg-surface-soft">
                        <div class="relative aspect-[4/3]">
                            <img src="{{ $photo->url }}" alt="{{ $photo->caption ?? $asset->assetName }}"
                                 class="h-full w-full object-cover"
                                 onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="absolute inset-0 hidden flex-col items-center justify-center gap-1 text-ink-muted">
                                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                                <span class="text-[10px]">No image</span>
                            </div>
                        </div>
                        @if ($photo->caption)
                            <figcaption class="truncate px-2.5 py-2 text-xs text-ink-soft">{{ $photo->caption }}</figcaption>
                        @endif
                    </figure>
                @endforeach
            </div>
        @else
            <div class="mt-3">
                <x-empty-state title="No photos available" message="No photographic records have been associated with this asset." />
            </div>
        @endif
    </x-card>
</div>
