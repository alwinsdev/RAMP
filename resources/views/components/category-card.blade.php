@props([
    'summary',       // App\DataObjects\CategorySummary
    'panchayatId',
])

@php
    use App\Enums\LifecycleStatus;
    $h = $summary->health;
    $base = max($h->total(), 1);
    $href = route('assets', ['panchayatId' => $panchayatId, 'categoryId' => $summary->id]);
@endphp

{{--
    Category card (CR-03 + CR-05) — large, touch-friendly, government-grade. The whole
    card is the click target and drills into the filtered Asset List. Shows the icon,
    name, total, a colour-coded health distribution bar, and Healthy/Near/Expired counts.
--}}
<a href="{{ $href }}" wire:navigate
   class="group flex min-h-[11.5rem] flex-col gap-4 rounded-2xl border border-hairline bg-surface p-5 shadow-[var(--shadow-card)] transition duration-200 hover:-translate-y-0.5 hover:border-brand/30 hover:shadow-[var(--shadow-hover)] focus:outline-none focus-visible:ring-2 focus-visible:ring-brand sm:p-6">

    {{-- Header: icon · name · description · chevron --}}
    <div class="flex items-start justify-between gap-3">
        <div class="flex items-start gap-3">
            <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl text-brand" style="background: var(--color-brand-tint);">
                <x-category-icon :id="$summary->id" class="h-6 w-6" />
            </span>
            <div class="min-w-0">
                <p class="font-bold leading-tight text-ink">{{ $summary->name }}</p>
                @if ($summary->description)
                    <p class="mt-0.5 truncate text-xs text-ink-muted">{{ $summary->description }}</p>
                @endif
            </div>
        </div>
        <svg class="h-5 w-5 shrink-0 text-ink-muted transition group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
    </div>

    {{-- Total --}}
    <div class="flex items-end gap-2">
        <span class="text-3xl font-extrabold leading-none tabular-nums text-ink">{{ $summary->total }}</span>
        <span class="pb-0.5 text-sm text-ink-soft">Total assets</span>
    </div>

    {{-- Health distribution bar --}}
    <div class="mt-auto flex flex-col gap-2.5">
        <div class="flex h-2.5 w-full overflow-hidden rounded-full bg-hairline-soft ring-hairline">
            @foreach (LifecycleStatus::displayOrder() as $status)
                @php $c = $h->count($status); @endphp
                @if ($c > 0)
                    <div class="h-full" style="width: {{ $c / $base * 100 }}%; background-color: {{ $status->color() }};" title="{{ $status->label() }}: {{ $c }}"></div>
                @endif
            @endforeach
        </div>

        {{-- Colour-coded status counts --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="flex items-center gap-1.5 rounded-lg px-2 py-1.5" style="background: color-mix(in srgb, {{ LifecycleStatus::Healthy->color() }} 10%, white);">
                <span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::Healthy->color() }};"></span>
                <span class="text-sm font-bold tabular-nums text-ink">{{ $h->healthy }}</span>
                <span class="text-[11px] text-ink-soft">Healthy</span>
            </div>
            <div class="flex items-center gap-1.5 rounded-lg px-2 py-1.5" style="background: color-mix(in srgb, {{ LifecycleStatus::NearExpiry->color() }} 12%, white);">
                <span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::NearExpiry->color() }};"></span>
                <span class="text-sm font-bold tabular-nums text-ink">{{ $h->nearExpiry }}</span>
                <span class="text-[11px] text-ink-soft">Near</span>
            </div>
            <div class="flex items-center gap-1.5 rounded-lg px-2 py-1.5" style="background: color-mix(in srgb, {{ LifecycleStatus::Expired->color() }} 10%, white);">
                <span class="h-2 w-2 rounded-full" style="background: {{ LifecycleStatus::Expired->color() }};"></span>
                <span class="text-sm font-bold tabular-nums text-ink">{{ $h->expired }}</span>
                <span class="text-[11px] text-ink-soft">Expired</span>
            </div>
        </div>
    </div>
</a>
