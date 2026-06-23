@props([
    'headers' => [],
    'resultCount' => null,
])

{{--
    Reusable table chrome for the Asset List (UI_RULES §8). Premium: rounded card
    container with hairline ring, sticky-feel header, zebra rows via row hover, and
    a result-count line. Consumers pass <tr> rows in the default slot and a mobile
    card list via the `cards` slot — the table hides below the tablet breakpoint
    (MR-01) and cards take over.

    $headers: array of ['label' => string, 'align' => 'left'|'center'|'right'].
--}}
@php
    $alignClass = ['left' => 'text-left', 'center' => 'text-center', 'right' => 'text-right'];
@endphp

<div {{ $attributes }}>
    @if (! is_null($resultCount))
        <p class="mb-3 text-sm text-ink-soft">
            Showing <span class="font-semibold text-ink tabular-nums">{{ $resultCount }}</span> {{ Str::plural('asset', $resultCount) }}
        </p>
    @endif

    {{-- Desktop / tablet table --}}
    <div class="hidden overflow-hidden rounded-xl border border-hairline bg-surface shadow-[var(--shadow-card)] sm:block">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-hairline bg-surface-soft text-[11px] font-semibold uppercase tracking-wider text-ink-muted">
                    @foreach ($headers as $header)
                        <th scope="col" class="px-4 py-3.5 {{ $alignClass[$header['align'] ?? 'left'] }}">{{ $header['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-hairline-soft">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Mobile card list (consumer-provided) --}}
    @isset($cards)
        <div class="flex flex-col gap-3 sm:hidden">
            {{ $cards }}
        </div>
    @endisset
</div>
