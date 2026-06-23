@props([
    'id' => null,   // category id, e.g. CAT-EDU
])

{{--
    Category recognition icon (UI_RULES CD-06 / DP-04) — one consistent outline glyph
    per asset category, reused everywhere the category appears. Falls back to a generic
    layers glyph for an unknown category.
--}}
@php
    $paths = [
        'CAT-EDU' => '<path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/>',
        'CAT-HLT' => '<path d="M19 14c1.5-1.5 3-3.4 3-5.5A4.5 4.5 0 0 0 12 6 4.5 4.5 0 0 0 2 8.5C2 12 5.5 15 12 20c2-1.5 3.7-2.9 5-4.2"/><path d="M3.5 11h4l1.5-3 2.5 6 1.5-3h4"/>',
        'CAT-WAT' => '<path d="M12 2.7s6 6.2 6 10.3a6 6 0 1 1-12 0c0-4.1 6-10.3 6-10.3z"/>',
        'CAT-PUB' => '<path d="M3 21h18M5 21V9l7-4 7 4v12M9 21v-5h6v5M8 12h.01M12 12h.01M16 12h.01"/>',
    ];
    $glyph = $paths[$id] ?? '<path d="M12 3 2 8.5 12 14l10-5.5L12 3z"/><path d="M2 13.5 12 19l10-5.5"/>';
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $glyph !!}
</svg>
