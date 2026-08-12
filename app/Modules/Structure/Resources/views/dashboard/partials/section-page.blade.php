{{-- Generic editor body for a standalone page section (fields, groups, repeatables, images). --}}
@php
    use App\Modules\Structure\Support\SectionRegistry;
    use Illuminate\Support\Str;

    $locales = SectionRegistry::locales();
    $images = $images ?? [];
    $localeLabels = ['ar' => __('dashboard.language_arabic'), 'en' => __('dashboard.language_english')];

    $imageFields = [];
    foreach ($section['fields'] ?? [] as $fieldName => $definition) {
        if (($definition['type'] ?? null) === 'image') {
            $imageFields[] = ['file_id' => $definition['file_id'] ?? $fieldName, 'name' => $fieldName, 'definition' => $definition];
        }
    }
    foreach ($section['groups'] ?? [] as $groupName => $group) {
        foreach ($group['fields'] ?? [] as $fieldName => $definition) {
            if (($definition['type'] ?? null) === 'image') {
                $imageFields[] = ['file_id' => $definition['file_id'] ?? $fieldName, 'name' => ($group['label'] ?? $groupName), 'definition' => $definition];
            }
        }
    }
    foreach ($section['shared'] ?? [] as $fieldName => $definition) {
        if (($definition['type'] ?? null) === 'image') {
            $imageFields[] = ['file_id' => $definition['file_id'] ?? $fieldName, 'name' => $fieldName, 'definition' => $definition];
        }
    }

    $sharedTextFields = array_filter($section['shared'] ?? [], fn (array $definition): bool => ($definition['type'] ?? 'text') !== 'image');
    $sharedRepeatables = array_filter($section['repeatables'] ?? [], fn (array $definition): bool => (bool) ($definition['shared'] ?? false));
    $localeRepeatables = array_filter($section['repeatables'] ?? [], fn (array $definition): bool => ! ($definition['shared'] ?? false));
@endphp

<x-dashboard.form-page
    :action="route('dashboard.structures.' . $sectionKey . '.store')"
    enctype="multipart/form-data"
    id="{{ $sectionKey }}-form"
>
    @if ($imageFields !== [])
        <h5 class="mb-3">{{ __('dashboard.image') }}</h5>
        <div class="row g-3 mb-4">
            @foreach ($imageFields as $image)
                @php $imageUrl = $images[$image['file_id']] ?? null; @endphp
                <div class="{{ $image['definition']['col'] ?? 'col-md-6' }}">
                    <label class="form-label">
                        {{ isset($image['definition']['label']) ? __($image['definition']['label']) : __($image['name']) }}
                    </label>
                    @if (! empty($imageUrl))
                        <div class="mb-2">
                            <img src="{{ $imageUrl }}" alt="" class="img-thumbnail" style="max-height: 120px" />
                        </div>
                    @endif
                    <input type="file" class="form-control" name="file[{{ $image['file_id'] }}]" accept="image/*" />
                    <input type="hidden" name="old[{{ $image['file_id'] }}]" value="{{ $imageUrl }}" />
                </div>
            @endforeach
        </div>
    @endif

    @if ($sharedTextFields !== [] || $sharedRepeatables !== [])
        <div class="row g-3 mb-3">
            @foreach ($sharedTextFields as $fieldName => $definition)
                @include('structure::dashboard.partials.section-field', [
                    'path' => "all.{$fieldName}",
                    'field' => $definition,
                    'default' => data_get($content, "all.{$fieldName}"),
                ])
            @endforeach
        </div>
        @foreach ($sharedRepeatables as $name => $definition)
            <div class="mb-4">
                @include('structure::dashboard.partials.section-repeatable', [
                    'itemPrefix' => "all[{$name}]",
                    'keyBase' => $name,
                    'fields' => $definition['fields'] ?? [],
                    'meta' => $definition['item'] ?? [],
                    'items' => old("all.{$name}", data_get($content, "all.{$name}", [])),
                    'max' => $definition['max'] ?? null,
                    'label' => $definition['label'] ?? null,
                ])
            </div>
        @endforeach
    @endif

    <ul class="nav nav-tabs mb-3" role="tablist">
        @foreach ($locales as $locale)
            <li class="nav-item">
                <button
                    class="nav-link {{ $loop->first ? 'active' : '' }}"
                    data-bs-toggle="tab"
                    data-bs-target="#{{ $sectionKey }}-{{ $locale }}"
                    type="button"
                >
                    {{ $localeLabels[$locale] ?? $locale }}
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content p-0">
        @foreach ($locales as $locale)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $sectionKey }}-{{ $locale }}">
                <div class="row g-3">
                    @foreach ($section['fields'] ?? [] as $fieldName => $definition)
                        @if (($definition['type'] ?? 'text') !== 'image')
                            @include('structure::dashboard.partials.section-field', [
                                'path' => "{$locale}.{$fieldName}",
                                'field' => $definition,
                                'default' => data_get($content, "{$locale}.{$fieldName}"),
                            ])
                        @endif
                    @endforeach
                </div>

                @foreach ($section['groups'] ?? [] as $groupName => $group)
                    <h6 class="mt-4 mb-2">
                        {{ isset($group['label']) ? __($group['label']) : Str::headline($groupName) }}
                    </h6>
                    <div class="row g-3">
                        @foreach ($group['fields'] ?? [] as $fieldName => $definition)
                            @if (($definition['type'] ?? 'text') !== 'image')
                                @include('structure::dashboard.partials.section-field', [
                                    'path' => "{$locale}.{$groupName}.{$fieldName}",
                                    'field' => $definition,
                                    'default' => data_get($content, "{$locale}.{$groupName}.{$fieldName}"),
                                ])
                            @endif
                        @endforeach
                    </div>
                @endforeach

                @foreach ($localeRepeatables as $name => $definition)
                    <div class="mt-4">
                        @include('structure::dashboard.partials.section-repeatable', [
                            'itemPrefix' => "{$locale}[{$name}]",
                            'keyBase' => $name,
                            'fields' => $definition['fields'] ?? [],
                            'meta' => $definition['item'] ?? [],
                            'items' => old("{$locale}.{$name}", data_get($content, "{$locale}.{$name}", [])),
                            'max' => $definition['max'] ?? null,
                            'label' => $definition['label'] ?? null,
                        ])
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
</x-dashboard.form-page>
