{{--
    Renders one non-image section field with x-dashboard.field.

    Props:
      $path    dot path into the payload, e.g. "ar.hero.title" or "all.phone"
      $field   the registry field definition (type, label, rules, col)
      $default stored fallback value (old() takes precedence)
--}}
@php
    $type = $field['type'] ?? 'text';
    $col = $field['col'] ?? 'col-md-6';
    $label = isset($field['label']) ? __($field['label']) : \Illuminate\Support\Str::headline(\Illuminate\Support\Str::afterLast($path, '.'));
    $isRequired = in_array('required', $field['rules'] ?? [], true);

    $segments = explode('.', $path);
    $htmlName = array_shift($segments);
    foreach ($segments as $segment) {
        $htmlName .= "[{$segment}]";
    }
@endphp

@if ($type === 'textarea')
    {{-- Structure textareas render as a Quill rich-text editor (see section.blade.php). --}}
    <x-dashboard.field
        :name="$htmlName"
        :label="$label"
        type="textarea"
        :value="old($path, $default ?? '')"
        :col="$col"
        :required="$isRequired"
        rows="6"
        data-rich-editor
    />
@else
    <x-dashboard.field
        :name="$htmlName"
        :label="$label"
        :type="$type"
        :value="old($path, $default ?? '')"
        :col="$col"
        :required="$isRequired"
    />
@endif
