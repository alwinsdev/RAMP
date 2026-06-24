@props([
    'asset',
])

@php
    use App\Enums\LifecycleStatus;

    $lc = $asset->lifecycle;
    $expectedLife = (int) config('ramp.lifecycle.expected_life', 25);
    $age = $lc?->currentAge;
    $isUnknown = ! $lc || $lc->status === LifecycleStatus::Unknown || $age === null;

    $percent = $isUnknown ? 0 : max(0, min(100, (int) round($age / max($expectedLife, 1) * 100)));
    $color = $isUnknown ? LifecycleStatus::Unknown->color() : $lc->status->color();
@endphp

{{-- Lifecycle progress indicator (CR-07 refinement) — "age / expected-life Years Used", colour-coded by health. --}}
<div class="flex flex-col gap-2">
    <div class="flex items-baseline justify-between">
        <span class="text-sm font-semibold text-ink-soft">Lifecycle progress</span>
        @if ($isUnknown)
            <span class="text-sm text-ink-muted">Not available</span>
        @else
            <span class="text-sm font-bold tabular-nums text-ink">{{ $age }} / {{ $expectedLife }} <span class="font-medium text-ink-soft">Years Used</span></span>
        @endif
    </div>

    <div class="h-3 w-full overflow-hidden rounded-full bg-hairline-soft ring-hairline">
        @unless ($isUnknown)
            <div class="h-full rounded-full transition-all" style="width: {{ max($percent, 3) }}%; background-color: {{ $color }};"></div>
        @endunless
    </div>

    @unless ($isUnknown)
        <p class="text-xs text-ink-muted">
            {{ $percent }}% of expected life used ·
            @if ($lc->remainingLife > 0)
                {{ $lc->remainingLife }} {{ \Illuminate\Support\Str::plural('year', $lc->remainingLife) }} remaining
            @else
                {{ abs($lc->remainingLife) }} {{ \Illuminate\Support\Str::plural('year', abs($lc->remainingLife)) }} past expected life
            @endif
        </p>
    @endunless
</div>
