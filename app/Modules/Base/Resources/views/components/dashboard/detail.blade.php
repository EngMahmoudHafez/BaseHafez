@props([
    'label',
    'value' => null,
])

{{-- One labelled cell of x-dashboard.details. Pass `:value` for a plain value
     or a default slot for custom markup (badges, links, ...). --}}
<div>
    <dt>{{ $label }}</dt>
    <dd>{{ $slot->isNotEmpty() ? $slot : (filled($value) ? $value : '—') }}</dd>
</div>
