@props([
    'title',
    'href' => null,
    'count' => null,
    'countLabel' => 'assets',
    'subtitle' => null,
    'accent' => '#1A73E8',   // per-level accent colour (district/zone/panchayat/category)
])

@php
    $wash = "background-image: linear-gradient(118deg, color-mix(in srgb, {$accent} 10%, white) 0%, var(--color-surface) 62%);";
    $chip = "background-image: linear-gradient(140deg, {$accent}, color-mix(in srgb, {$accent} 68%, #000 12%));";
    $countStyle = "color: color-mix(in srgb, {$accent} 78%, #000 18%);";
@endphp

{{--
    Hierarchy node card (District / Zone / Panchayat / Category). A clickable
    drill-down doorway: colour wash + a solid gradient icon chip (white glyph), the
    node name, an optional sub-line, and its scoped asset count with a hover chevron.
--}}
<x-card :href="$href" class="flex items-center justify-between gap-4 overflow-hidden" style="{{ $wash }}">
    <span class="absolute inset-y-0 left-0 w-1" style="background-image: linear-gradient(180deg, {{ $accent }}, color-mix(in srgb, {{ $accent }} 45%, white));"></span>

    <div class="flex min-w-0 items-center gap-3 pl-1">
        @isset($icon)
            <span class="grid h-11 w-11 shrink-0 place-items-center rounded-xl text-white shadow-sm ring-1 ring-white/30" style="{{ $chip }}">
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
                <p class="text-xl font-extrabold leading-none tabular-nums" style="{{ $countStyle }}">{{ $count }}</p>
                <p class="mt-1 text-[11px] text-ink-muted">{{ \Illuminate\Support\Str::plural($countLabel, (int) $count) }}</p>
            </div>
        @endunless
        <svg class="h-5 w-5 transition group-hover:translate-x-0.5" style="color: {{ $accent }};" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
    </div>
</x-card>
