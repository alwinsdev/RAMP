@props([
    'href' => null,
    'padding' => 'p-5 sm:p-6',
])

{{--
    Premium surface card (UI_RULES §7 + UI_DESIGN_SYSTEM). Crisp hairline edge,
    soft elevation, and — when $href is set — a clickable drill-down doorway with a
    refined hover lift and SPA navigation (CD-03).
--}}
@php
    $base = "group relative block rounded-xl border border-hairline bg-surface {$padding} transition duration-200";
    $shadow = 'shadow-[var(--shadow-card)]';
    $interactive = $href
        ? ' hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-[var(--shadow-hover)] focus:outline-none'
        : '';
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $base.' '.$shadow.$interactive]) }}>
        {{ $slot }}
        @isset($arrow)
            {{ $arrow }}
        @endisset
    </a>
@else
    <div {{ $attributes->merge(['class' => $base.' '.$shadow]) }}>
        {{ $slot }}
    </div>
@endif
