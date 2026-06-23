@props(['trail' => []])

{{--
    Breadcrumb wayfinding (UI_RULES §6, BR-NV-04). Pass an ordered $trail of
    ['label' => string, 'url' => ?string]. First item is Home; every item except
    the last is a link, the last is plain text (BC-03). Chevron delimiters.
--}}
@php $items = collect($trail)->values(); @endphp

<nav aria-label="Breadcrumb">
    <ol class="flex flex-wrap items-center gap-x-1 gap-y-1 text-[13px]">
        @foreach ($items as $i => $crumb)
            @php $isLast = $i === $items->count() - 1; @endphp
            <li class="flex items-center gap-x-1">
                @if (! $isLast && ! empty($crumb['url']))
                    <a href="{{ $crumb['url'] }}" wire:navigate
                       class="rounded-md px-1.5 py-0.5 font-medium text-ink-soft transition hover:bg-brand-tint hover:text-brand">
                        {{ $crumb['label'] }}
                    </a>
                @else
                    <span @class(['px-1.5 py-0.5', 'font-semibold text-ink' => $isLast, 'text-ink-soft' => ! $isLast]) @if($isLast) aria-current="page" @endif>
                        {{ $crumb['label'] }}
                    </span>
                @endif

                @unless ($isLast)
                    <svg class="h-3.5 w-3.5 text-ink-muted/60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6l6 6-6 6" /></svg>
                @endunless
            </li>
        @endforeach
    </ol>
</nav>
