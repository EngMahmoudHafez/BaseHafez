@props([
    'name',
    'label' => null,
    'type' => 'text',
    'value' => null,
    'options' => [],
    'required' => false,
    'col' => 'col-md-6',
    'placeholder' => null,
    'help' => null,
    'readonly' => false,
    'disabled' => false,
    'selected' => null,
])

@php
    $id = $attributes->get('id', $name);
    $hasError = $errors->has($name);
    $current = old($name, $value);
    $selectedValue = $selected ?? $current;
    $errorId = $id . '-error';
    // Everything except `id` is forwarded onto the control (class, rows, step,
    // accept, min/max, data-*, ...); `id` is rendered explicitly to avoid a dupe.
    $control = $attributes->except('id');
    // Accessibility: mark the invalid control and point screen readers at the message.
    if ($hasError) {
        $control = $control->merge(['aria-invalid' => 'true', 'aria-describedby' => $errorId]);
    }
@endphp

{{-- Unified form field. Renders the wrapper column, label (with a `*` when
     required), the control for `type`, optional help text and the validation
     message. Pass a default slot to supply a fully custom control. --}}
<div class="{{ $col }}">
    @if ($label && $type !== 'checkbox')
        <label class="form-label" for="{{ $id }}">
            {{ $label }}
            @if ($required)
                <span class="text-danger"> *</span>
            @endif
        </label>
    @endif

    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $control->class(['form-control', 'is-invalid' => $hasError]) }}
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($readonly) readonly @endif
            @if ($disabled) disabled @endif
        >{{ $current }}</textarea>
    @elseif ($type === 'select')
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $control->class(['form-select', 'is-invalid' => $hasError]) }}
            @if ($required) required @endif
            @if ($disabled) disabled @endif
        >
            @if ($placeholder !== null)
                <option value="">{{ $placeholder }}</option>
            @endif
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) $selectedValue === (string) $optionValue)>
                    {{ $optionLabel }}
                </option>
            @endforeach
        </select>
    @elseif ($type === 'checkbox')
        <div class="form-check">
            <input type="hidden" name="{{ $name }}" value="0" />
            <input
                type="checkbox"
                id="{{ $id }}"
                name="{{ $name }}"
                value="1"
                {{ $control->class(['form-check-input', 'is-invalid' => $hasError]) }}
                @checked((bool) old($name, $value))
                @if ($required) required @endif
                @if ($disabled) disabled @endif
            />
            @if ($label)
                <label class="form-check-label" for="{{ $id }}">{{ $label }}</label>
            @endif
        </div>
    @elseif ($type === 'file')
        <input
            type="file"
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $control->class(['form-control', 'is-invalid' => $hasError]) }}
            @if ($required) required @endif
            @if ($disabled) disabled @endif
        />
    @else
        <input
            type="{{ $type }}"
            id="{{ $id }}"
            name="{{ $name }}"
            value="{{ $current }}"
            {{ $control->class(['form-control', 'is-invalid' => $hasError]) }}
            @if ($placeholder) placeholder="{{ $placeholder }}" @endif
            @if ($required) required @endif
            @if ($readonly) readonly @endif
            @if ($disabled) disabled @endif
        />
    @endif

    @if ($help)
        <div class="form-text">{{ $help }}</div>
    @endif

    @error($name)
        <div class="invalid-feedback d-block" id="{{ $errorId }}">{{ $message }}</div>
    @enderror
</div>
