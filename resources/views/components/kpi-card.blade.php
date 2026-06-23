@props([
    'label',
    'value',
    'sublabel' => null,
    'href' => null,
    'icon' => null,
])

{{--
    Dashboard KPI card (UI_RULES §6.2): premium treatment with an eyebrow label,
    a large tabular number, an optional tinted icon chip, and a drill-down arrow
    that slides on hover when clickable (DB-04).
--}}
<x-card :href="$href" class="overflow-hidden">
    <div class="flex items-start justify-between gap-3">
        <span class="eyebrow">{{ $label }}</span>
        @if ($icon)
            <span class="grid h-9 w-9 place-items-center rounded-lg text-brand" style="background: var(--color-brand-tint);">
                {!! $icon !!}
            </span>
        @endif
    </div>

    <div class="mt-3 flex items-end gap-2">
        <span class="text-[2rem] font-extrabold leading-none tracking-tight tabular-nums text-ink sm:text-4xl">{{ $value }}</span>
    </div>

    <div class="mt-2 flex items-center justify-between">
        @if ($sublabel)
            <span class="text-sm text-ink-soft">{{ $sublabel }}</span>
        @else
            <span></span>
        @endif

        @if ($href)
            <span class="inline-flex items-center gap-1 text-xs font-semibold text-brand opacity-0 transition group-hover:translate-x-0.5 group-hover:opacity-100">
                View
                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
            </span>
        @endif
    </div>
</x-card>
