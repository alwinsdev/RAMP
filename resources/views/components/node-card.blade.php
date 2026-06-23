@props([
    'title',
    'href' => null,
    'count' => null,
    'countLabel' => 'assets',
    'subtitle' => null,
])

{{--
    Hierarchy node card (District / Zone / Panchayat / Category). A clickable
    drill-down doorway showing the node name, an optional icon, an optional
    sub-line, and its scoped asset count with a hover chevron.
--}}
<x-card :href="$href" class="flex items-center justify-between gap-4">
    <div class="flex min-w-0 items-start gap-3">
        @isset($icon)
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-brand" style="background: var(--color-brand-tint);">
                {{ $icon }}
            </span>
        @endisset
        <div class="min-w-0">
            <p class="truncate font-semibold text-ink">{{ $title }}</p>
            @if ($subtitle)
                <p class="mt-0.5 truncate text-sm text-ink-soft">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    <div class="flex shrink-0 items-center gap-3">
        @unless (is_null($count))
            <div class="text-right">
                <p class="text-lg font-bold leading-none tabular-nums text-ink">{{ $count }}</p>
                <p class="mt-1 text-[11px] text-ink-muted">{{ \Illuminate\Support\Str::plural($countLabel, (int) $count) }}</p>
            </div>
        @endunless
        <svg class="h-5 w-5 text-ink-muted transition group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
    </div>
</x-card>
