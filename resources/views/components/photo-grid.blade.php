@props([
    'photos' => [],      // array of App\DataObjects\PhotoData
    'assetName' => '',
    'columns' => 'grid-cols-2 sm:grid-cols-3 lg:grid-cols-4',
])

@php
    $items = collect($photos)->map(fn ($p) => ['url' => $p->url, 'caption' => $p->caption ?? $assetName])->values();
@endphp

{{--
    Reusable photo grid with an Alpine modal lightbox (CR-07 #6). Thumbnails open an
    enlarged modal with prev/next, captions, and keyboard control. Missing images
    show a placeholder. Reused by the Photo Gallery and the Asset Information screen.
--}}
@if (count($items) === 0)
    <x-empty-state title="No photos available" message="No photographic records have been associated with this asset." />
@else
    <div
        x-data="{
            photos: @js($items),
            index: null,
            open(i) { this.index = i },
            close() { this.index = null },
            prev() { this.index = (this.index - 1 + this.photos.length) % this.photos.length },
            next() { this.index = (this.index + 1) % this.photos.length },
        }"
        @keydown.escape.window="close()"
        @keydown.arrow-left.window="index !== null && prev()"
        @keydown.arrow-right.window="index !== null && next()"
    >
        <div class="grid {{ $columns }} gap-3">
            @foreach ($items as $i => $photo)
                <button type="button" @click="open({{ $i }})"
                        class="group overflow-hidden rounded-xl border border-hairline bg-surface-soft text-left shadow-[var(--shadow-card)] transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-hover)] focus:outline-none focus-visible:ring-2 focus-visible:ring-brand">
                    <div class="relative aspect-[4/3]">
                        <img src="{{ $photo['url'] }}" alt="{{ $photo['caption'] }}" class="h-full w-full object-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                        <div class="absolute inset-0 hidden flex-col items-center justify-center gap-1 text-ink-muted">
                            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="9" cy="9" r="2"/><path d="m21 15-3.1-3.1a2 2 0 0 0-2.8 0L6 21"/></svg>
                            <span class="text-[10px]">No image</span>
                        </div>
                    </div>
                    <p class="truncate px-2.5 py-2 text-xs text-ink-soft">{{ $photo['caption'] }}</p>
                </button>
            @endforeach
        </div>

        {{-- Lightbox modal --}}
        <template x-teleport="body">
            <div x-show="index !== null" x-cloak class="fixed inset-0 flex flex-col bg-black/80 p-4 backdrop-blur-sm" style="z-index: 9999;" @click.self="close()" x-transition.opacity>
                <div class="flex justify-end">
                    <button type="button" @click="close()" class="grid h-10 w-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Close">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <div class="flex flex-1 items-center justify-center gap-3">
                    <button type="button" @click="prev()" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Previous">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                    </button>
                    <div class="flex max-h-full max-w-3xl flex-1 flex-col items-center gap-3">
                        <img :src="index !== null ? photos[index].url : ''" :alt="index !== null ? photos[index].caption : ''" class="max-h-[70vh] w-auto rounded-lg object-contain" @load="$el.style.visibility='visible'" onerror="this.style.visibility='hidden';">
                        <p class="text-center text-sm text-white/80" x-text="index !== null ? photos[index].caption : ''"></p>
                    </div>
                    <button type="button" @click="next()" class="grid h-11 w-11 shrink-0 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20" aria-label="Next">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                    </button>
                </div>
            </div>
        </template>
    </div>
@endif
