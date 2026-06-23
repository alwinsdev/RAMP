@props([
    'status',
    'size' => 'sm', // sm = list/table pill, lg = detail banner
])

@php
    use App\Enums\LifecycleStatus;

    // Always render a status; a null/absent computation falls back to Unknown (BR-HL-04).
    $status = $status instanceof LifecycleStatus ? $status : LifecycleStatus::Unknown;
    $color = $status->color();

    $dims = $size === 'lg'
        ? 'gap-2 px-3.5 py-1.5 text-sm'
        : 'gap-1.5 px-2.5 py-1 text-xs';
@endphp

{{--
    Canonical status pill — the ONLY way status is rendered (UI_RULES §3.1, CO-03).
    Premium soft-tint treatment: a faint wash of the status color, a saturated dot,
    and the label in the status color. Color is always paired with the text label
    (AX-01); the component never computes status itself.
--}}
<span
    {{ $attributes->merge(['class' => "inline-flex items-center {$dims} rounded-full font-semibold whitespace-nowrap ring-1 ring-inset"]) }}
    style="color: {{ $color }}; background-color: color-mix(in srgb, {{ $color }} 12%, white); --tw-ring-color: color-mix(in srgb, {{ $color }} 28%, white);"
>
    <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $color }};" aria-hidden="true"></span>
    {{ $status->label() }}
</span>
