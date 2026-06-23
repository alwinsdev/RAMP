@props([
    'title',
    'rows' => [],
    'emptyMessage' => 'No assets recorded yet.',
])

{{--
    Dashboard breakdown card (zone / panchayat / category). Each row is a drill-down
    doorway into the Asset List filtered by that dimension (BR-NV-06 / DB-10). Rows
    are App\DataObjects\Breakdown (id, name, count, filterKey).
--}}
<x-card class="flex flex-col gap-1 !p-0">
    <div class="flex items-center justify-between px-5 pb-2 pt-5">
        <span class="eyebrow">{{ $title }}</span>
        <span class="text-xs text-ink-muted">{{ count($rows) }} {{ \Illuminate\Support\Str::plural('row', count($rows)) }}</span>
    </div>

    @if (count($rows) === 0)
        <p class="px-5 pb-5 text-sm text-ink-soft">{{ $emptyMessage }}</p>
    @else
        <ul class="flex flex-col">
            @foreach ($rows as $row)
                <li>
                    <a href="{{ route('assets', [$row->filterKey => $row->id]) }}" wire:navigate
                       class="group flex items-center justify-between gap-3 border-t border-hairline-soft px-5 py-2.5 transition hover:bg-surface-soft">
                        <span class="truncate text-sm text-ink">{{ $row->name }}</span>
                        <span class="flex items-center gap-2 shrink-0">
                            <span class="text-sm font-semibold tabular-nums text-ink">{{ $row->count }}</span>
                            <svg class="h-4 w-4 text-ink-muted transition group-hover:translate-x-0.5 group-hover:text-brand" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 6l6 6-6 6"/></svg>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    @endif
</x-card>
