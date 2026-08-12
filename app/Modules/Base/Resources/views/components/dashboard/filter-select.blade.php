@props([
    'name',
    'label' => null,
    'options' => [],
    'selected' => null,
    'placeholder' => null,
    'col' => 'col-12 col-lg-3',
])

@php
    $selected = $selected ?? request($name);
    $placeholder = $placeholder ?? __('dashboard.all');
@endphp

{{-- Labelled select for the filter bar. `options` is a value => label map and
     the `placeholder` becomes the empty "all" option. --}}
<div class="{{ $col }}">
    @if ($label)
        <label class="form-label" for="{{ $name }}">{{ $label }}</label>
    @endif
    <select {{ $attributes->class(['form-select']) }} id="{{ $name }}" name="{{ $name }}">
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $value => $optionLabel)
            <option value="{{ $value }}" @selected((string) $selected === (string) $value)>{{ $optionLabel }}</option>
        @endforeach
    </select>
</div>
