@props([
    'label',
    'value' => null,
    'mono' => false,
])

{{-- A label/value row inside an Asset Detail information panel. --}}
<div class="flex items-start justify-between gap-4 border-b border-hairline-soft py-2.5 last:border-0">
    <dt class="text-sm text-ink-soft">{{ $label }}</dt>
    <dd @class(['text-right text-sm font-medium text-ink', 'font-mono' => $mono])>{{ $value ?? '—' }}</dd>
</div>
