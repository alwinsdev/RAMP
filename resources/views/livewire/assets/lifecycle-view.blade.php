@php
    use App\Enums\LifecycleStatus;
    $lc = $asset->lifecycle;
    $isUnknown = ! $lc || $lc->status === LifecycleStatus::Unknown;
@endphp

<div class="flex flex-col gap-6">
    <x-breadcrumb :trail="$breadcrumbs" />

    <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div class="flex flex-col gap-1">
            <span class="eyebrow">Lifecycle · {{ $asset->assetName }}</span>
            <h1 class="text-2xl font-bold tracking-tight text-ink sm:text-3xl">Lifecycle Monitoring</h1>
        </div>
        <a href="{{ route('assets.show', ['asset' => $asset->id]) }}" wire:navigate class="inline-flex w-fit items-center gap-1.5 text-sm font-semibold text-brand hover:text-brand-hover">
            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M11 18l-6-6 6-6"/></svg>
            Back to detail
        </a>
    </div>

    <div class="grid gap-4 lg:grid-cols-5">
        {{-- Life-consumed gauge --}}
        <x-card class="lg:col-span-2 flex flex-col items-center justify-center gap-3">
            <span class="eyebrow self-start">Life consumed</span>
            @if (! is_null($consumedPercent) && ! $isUnknown)
                <div wire:ignore class="w-full"
                     x-data="lifecycleGauge({ percent: {{ $consumedPercent }}, color: @js($lc->status->color()), label: 'Life consumed' })">
                    <div x-ref="gauge"></div>
                </div>
                <x-status-badge :status="$lc->status" size="lg" />
            @else
                <div class="flex flex-col items-center gap-3 py-10 text-center">
                    <x-status-badge :status="$lc?->status" size="lg" />
                    <p class="max-w-xs text-sm text-ink-soft">Lifecycle inputs are missing or invalid, so age and remaining life cannot be computed.</p>
                </div>
            @endif
        </x-card>

        {{-- Figures --}}
        <x-card class="lg:col-span-3">
            <span class="eyebrow">Lifecycle figures</span>
            <dl class="mt-3 grid grid-cols-2 gap-x-6">
                <x-detail-row label="Construction Year" :value="$asset->constructionYear ? (string) $asset->constructionYear : '—'" />
                <x-detail-row label="Expected Life" :value="$asset->expectedLife ? $asset->expectedLife.' yr' : '—'" />
                <x-detail-row label="Current Age" :value="is_null($lc?->currentAge) ? '—' : $lc->currentAge.' yr'" />
                <x-detail-row label="Remaining Life" :value="is_null($lc?->remainingLife) ? '—' : $lc->remainingLife.' yr'" />
            </dl>

            <div class="mt-5 rounded-lg border border-hairline-soft bg-surface-soft p-4">
                <p class="text-xs font-semibold uppercase tracking-wide text-ink-muted">How status is derived</p>
                <ul class="mt-2 flex flex-col gap-1.5 text-sm text-ink-soft">
                    <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Healthy->color() }}"></span> Healthy — remaining life &gt; 5 years</li>
                    <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::NearExpiry->color() }}"></span> Near Expiry — 0 &lt; remaining life ≤ 5 years</li>
                    <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Expired->color() }}"></span> Expired — remaining life ≤ 0</li>
                    <li class="flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full" style="background: {{ LifecycleStatus::Unknown->color() }}"></span> Unknown — inputs missing or invalid</li>
                </ul>
            </div>
        </x-card>
    </div>
</div>
