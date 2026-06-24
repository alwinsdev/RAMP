@props([
    'id' => null,   // category id, e.g. CAT-PRI
])

{{--
    Category recognition icon (UI_RULES CD-06 / DP-04) — one consistent outline glyph
    per asset category, reused everywhere the category appears. Falls back to a generic
    layers glyph for an unknown category.
--}}
@php
    $paths = [
        // Primary Schools — school building with flag
        'CAT-PRI' => '<path d="M22 10L12 5 2 10l10 5 10-5z"/><path d="M6 12v5c0 1 2.7 2.5 6 2.5s6-1.5 6-2.5v-5"/><path d="M12 5V2"/>',
        // Nursery Schools — building with heart
        'CAT-NUR' => '<path d="M3 21V8l9-5 9 5v13"/><path d="M9 21v-5h6v5"/><path d="M12 9.5c.8-1 2.5-.6 2.5.8 0 1-1.3 1.8-2.5 2.7-1.2-.9-2.5-1.7-2.5-2.7 0-1.4 1.7-1.8 2.5-.8z"/>',
        // Play Schools — child play / blocks
        'CAT-PLY' => '<rect x="3" y="13" width="8" height="8" rx="1"/><rect x="13" y="13" width="8" height="8" rx="1"/><path d="M8 13V9a4 4 0 0 1 8 0v4"/><circle cx="12" cy="5" r="2"/>',
        // Toilet Buildings — building with W/C door
        'CAT-TOI' => '<rect x="4" y="3" width="16" height="18" rx="1.5"/><path d="M9 3v18M15 3v18"/><circle cx="6.5" cy="12" r="0.6" fill="currentColor"/><circle cx="17.5" cy="12" r="0.6" fill="currentColor"/>',
        // Overhead Water Tank — tank on legs with drop
        'CAT-OHT' => '<path d="M6 3h12l-1.5 6h-9L6 3z"/><path d="M7 9v3M17 9v3"/><path d="M7 12h10l-1 9M7 12l1 9"/><path d="M12 5.5c.7.8 1.2 1.5 1.2 2.1a1.2 1.2 0 0 1-2.4 0c0-.6.5-1.3 1.2-2.1z" fill="currentColor"/>',
        // Underground Water Tank — ground line + buried tank + drop
        'CAT-UGT' => '<path d="M3 9h18"/><path d="M6 9v8a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V9"/><path d="M12 12.5c.8 1 1.4 1.8 1.4 2.5a1.4 1.4 0 0 1-2.8 0c0-.7.6-1.5 1.4-2.5z" fill="currentColor"/>',
        // Ration Shops — store with awning
        'CAT-RAT' => '<path d="M3 9l1.5-4h15L21 9"/><path d="M3 9h18v3a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0V9z"/><path d="M5 12v9h14v-9"/><path d="M10 21v-5h4v5"/>',
        // Panchayat Offices — government building with columns
        'CAT-PAN' => '<path d="M3 21h18M5 21V9l7-4 7 4v12M9 21v-6h6v6"/><path d="M8 12h.01M12 12h.01M16 12h.01"/>',
        // Function Halls — hall with arch entrance
        'CAT-FUN' => '<path d="M3 21V10l9-6 9 6v11"/><path d="M9 21v-6a3 3 0 0 1 6 0v6"/><path d="M3 10h18"/>',
        // Bore Wells — well + hand pump
        'CAT-BOR' => '<path d="M7 21h10"/><path d="M9 21V8h2v13"/><path d="M11 9h6l-2-3"/><path d="M11 12h4"/><circle cx="9" cy="6" r="2"/>',
    ];
    $glyph = $paths[$id] ?? '<path d="M12 3 2 8.5 12 14l10-5.5L12 3z"/><path d="M2 13.5 12 19l10-5.5"/>';
@endphp

<svg {{ $attributes->merge(['class' => 'h-5 w-5']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    {!! $glyph !!}
</svg>
