<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <span class="eyebrow">Photos · {{ $asset->assetName }}</span>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Photo Gallery</h1>
            <p class="text-sm text-ink-soft">{{ count($asset->photos) }} {{ \Illuminate\Support\Str::plural('photo', count($asset->photos)) }}</p>
        </div>
        <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to information
        </a>
    </div>

    <x-photo-grid :photos="$asset->photos" :asset-name="$asset->assetName" />
</div>
