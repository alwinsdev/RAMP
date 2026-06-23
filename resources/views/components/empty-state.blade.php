@props([
    'title' => 'Nothing to show',
    'message' => null,
])

{{--
    Reusable empty / no-results state (SCR-11, UI_RULES §10). Used by every list,
    grid, gallery, and map so a "no data" condition is never a dead-end (BR-NV-08).
    Premium: soft tinted icon medallion, generous spacing, optional action slot.
--}}
<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center gap-3.5 rounded-xl border border-dashed border-hairline bg-surface px-6 py-14 text-center shadow-[var(--shadow-card)]']) }}>
    <div class="grid h-14 w-14 place-items-center rounded-2xl text-brand" style="background: var(--color-brand-tint);" aria-hidden="true">
        <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v11a2 2 0 0 1-2 2z" />
        </svg>
    </div>
    <h3 class="text-base font-semibold text-ink">{{ $title }}</h3>
    @if ($message)
        <p class="max-w-sm text-sm text-ink-soft">{{ $message }}</p>
    @endif
    @isset($action)
        <div class="mt-1">{{ $action }}</div>
    @endisset
</div>
