@props(['asset'])

{{--
    Asset summary card — the mobile representation of an Asset List row (UI_RULES
    CD-04 / MR-01). Content priority: what it is (name + number) → status → where
    (panchayat) → supporting detail.
--}}
<a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate
   class="group flex flex-col gap-3 rounded-xl border border-hairline bg-surface p-4 shadow-[var(--shadow-card)] transition hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-[var(--shadow-hover)]">
    <div class="flex items-start justify-between gap-3">
        <div class="min-w-0">
            <p class="truncate font-semibold text-ink">{{ $asset->assetName }}</p>
            <p class="mt-0.5 font-mono text-xs text-ink-muted">{{ $asset->assetNumber }}</p>
        </div>
        <x-status-badge :status="$asset->lifecycle?->status" />
    </div>
    <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-ink-soft">
        <span class="inline-flex items-center gap-1.5"><x-category-icon :id="$asset->categoryId" class="h-3.5 w-3.5" /> {{ $asset->assetType }}</span>
        <span aria-hidden="true" class="text-hairline">•</span>
        <span>{{ $asset->panchayatName }}</span>
    </div>
</a>
