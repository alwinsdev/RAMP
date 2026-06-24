@props([
    'label',
    'value',
    'sublabel' => null,
    'href' => null,
    'icon' => null,
    'accent' => '#1A73E8',   // card accent (brand blue by default, or a status colour)
])

@php
    // A soft, premium colour wash + a solid gradient icon chip + a top accent bar —
    // colourful but calm, driven entirely by the single $accent value.
    $wash = "background-image: linear-gradient(157deg, color-mix(in srgb, {$accent} 13%, white) 0%, var(--color-surface) 58%);";
    $chip = "background-image: linear-gradient(140deg, {$accent}, color-mix(in srgb, {$accent} 68%, #000 12%));";
    $bar  = "background-image: linear-gradient(90deg, {$accent}, color-mix(in srgb, {$accent} 35%, white));";
@endphp

{{--
    Dashboard KPI card (UI_RULES §6.2): top accent bar, tinted colour wash, a solid
    gradient icon chip with a white glyph, a large tabular number, and a drill-down
    arrow (DB-04). Every colour derives from $accent so the set stays consistent.
--}}
<x-card :href="$href" class="overflow-hidden" style="{{ $wash }}">
    <span class="absolute inset-x-0 top-0 h-1" style="{{ $bar }}"></span>

    <div class="flex items-start justify-between gap-3">
        <span class="eyebrow">{{ $label }}</span>
        @if ($icon)
            <span class="grid h-10 w-10 shrink-0 place-items-center rounded-xl text-white shadow-sm ring-1 ring-white/30" style="{{ $chip }}">
                {!! $icon !!}
            </span>
        @endif
    </div>

    <div class="mt-3 text-[2rem] font-extrabold leading-none tracking-tight tabular-nums text-ink sm:text-[2.25rem]">{{ $value }}</div>

    <div class="mt-2 flex min-h-[1.25rem] items-center justify-between">
        @if ($sublabel)
            <span class="text-xs font-medium text-ink-soft">{{ $sublabel }}</span>
        @else
            <span></span>
        @endif

        @if ($href)
            <span class="inline-flex items-center gap-1 text-xs font-semibold opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100" style="color: {{ $accent }};">
                View
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </span>
        @endif
    </div>
</x-card>
