{{--
    Generic add/remove repeatable list (one shared partial, one shared handler in
    section-scripts). Renders existing rows plus a <template> the script clones.

    Props:
      $itemPrefix  full bracket prefix before the index, e.g. "ar[blocks]",
                   "all[social_links]" or "ar[advantages][items]"
      $keyBase     base string for auto-generated item keys
      $fields      item field definitions (text / textarea)
      $meta        enabled item meta: any of key, sort_order, visible, rating
      $items       existing rows
      $max         maximum rows
      $label       optional heading translation key
--}}
@php
    $meta = $meta ?? [];
    $items = $items ?? [];
    $keyBase = $keyBase ?? 'item';
    $max = $max ?? \App\Modules\Structure\Support\SectionRegistry::MAX_ITEMS;
@endphp

<div
    class="section-repeatable"
    data-item-prefix="{{ $itemPrefix }}"
    data-key-base="{{ $keyBase }}"
    data-max="{{ $max }}"
>
    @isset($label)
        <label class="form-label fw-semibold">{{ __($label) }}</label>
    @endisset

    <div class="section-repeatable-list">
        @foreach ($items as $index => $item)
            <div class="card section-repeatable-item mb-3 border">
                <div class="card-body">
                    @if (in_array('key', $meta, true))
                        <input
                            type="hidden"
                            name="{{ $itemPrefix }}[{{ $index }}][key]"
                            value="{{ $item['key'] ?? $keyBase . '-' . ($index + 1) }}"
                        />
                    @endif
                    <div class="row g-3">
                        @if (in_array('sort_order', $meta, true))
                            <div class="col-md-2">
                                <label class="form-label">{{ __('dashboard.Order') }}</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    min="1"
                                    name="{{ $itemPrefix }}[{{ $index }}][sort_order]"
                                    value="{{ $item['sort_order'] ?? $index + 1 }}"
                                    required
                                />
                            </div>
                        @endif
                        @if (in_array('rating', $meta, true))
                            <div class="col-md-2">
                                <label class="form-label">{{ __('dashboard.homepage_fields.rating') }}</label>
                                <input
                                    class="form-control"
                                    type="number"
                                    min="1"
                                    max="5"
                                    name="{{ $itemPrefix }}[{{ $index }}][rating]"
                                    value="{{ $item['rating'] ?? 5 }}"
                                    required
                                />
                            </div>
                        @endif
                        @foreach ($fields as $field => $definition)
                            <div class="{{ $definition['col'] ?? (($definition['type'] ?? 'text') === 'textarea' ? 'col-12' : 'col-md-5') }}">
                                <label class="form-label">{{ isset($definition['label']) ? __($definition['label']) : \Illuminate\Support\Str::headline($field) }}</label>
                                @if (($definition['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        class="form-control"
                                        rows="3"
                                        name="{{ $itemPrefix }}[{{ $index }}][{{ $field }}]"
                                        @required(in_array('required', $definition['rules'] ?? [], true))
                                    >{{ $item[$field] ?? '' }}</textarea>
                                @else
                                    <input
                                        class="form-control"
                                        type="{{ $definition['type'] ?? 'text' }}"
                                        name="{{ $itemPrefix }}[{{ $index }}][{{ $field }}]"
                                        value="{{ $item[$field] ?? '' }}"
                                        @required(in_array('required', $definition['rules'] ?? [], true))
                                    />
                                @endif
                            </div>
                        @endforeach
                        @if (in_array('visible', $meta, true))
                            <div class="col-md-3">
                                <input type="hidden" name="{{ $itemPrefix }}[{{ $index }}][visible]" value="0" />
                                <label class="form-check mt-4">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="{{ $itemPrefix }}[{{ $index }}][visible]"
                                        value="1"
                                        @checked((bool) ($item['visible'] ?? true))
                                    />
                                    <span class="form-check-label">{{ __('dashboard.Visible') }}</span>
                                </label>
                            </div>
                        @endif
                        <div class="col-12 text-end">
                            <button type="button" class="btn btn-outline-danger btn-sm section-repeatable-remove">
                                {{ __('dashboard.Delete') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <button type="button" class="btn btn-outline-primary btn-sm section-repeatable-add">
        {{ __('dashboard.Add Item') }}
    </button>

    <template>
        <div class="card section-repeatable-item mb-3 border">
            <div class="card-body">
                @if (in_array('key', $meta, true))
                    <input type="hidden" data-name="key" />
                @endif
                <div class="row g-3">
                    @if (in_array('sort_order', $meta, true))
                        <div class="col-md-2">
                            <label class="form-label">{{ __('dashboard.Order') }}</label>
                            <input class="form-control" type="number" min="1" data-name="sort_order" required />
                        </div>
                    @endif
                    @if (in_array('rating', $meta, true))
                        <div class="col-md-2">
                            <label class="form-label">{{ __('dashboard.homepage_fields.rating') }}</label>
                            <input
                                class="form-control"
                                type="number"
                                min="1"
                                max="5"
                                data-name="rating"
                                value="5"
                                required
                            />
                        </div>
                    @endif
                    @foreach ($fields as $field => $definition)
                        <div class="{{ $definition['col'] ?? (($definition['type'] ?? 'text') === 'textarea' ? 'col-12' : 'col-md-5') }}">
                            <label class="form-label">{{ isset($definition['label']) ? __($definition['label']) : \Illuminate\Support\Str::headline($field) }}</label>
                            @if (($definition['type'] ?? 'text') === 'textarea')
                                <textarea
                                    class="form-control"
                                    rows="3"
                                    data-name="{{ $field }}"
                                    @required(in_array('required', $definition['rules'] ?? [], true))
                                ></textarea>
                            @else
                                <input
                                    class="form-control"
                                    type="{{ $definition['type'] ?? 'text' }}"
                                    data-name="{{ $field }}"
                                    @required(in_array('required', $definition['rules'] ?? [], true))
                                />
                            @endif
                        </div>
                    @endforeach
                    @if (in_array('visible', $meta, true))
                        <div class="col-md-3">
                            <input type="hidden" data-name="visible" value="0" />
                            <label class="form-check mt-4">
                                <input class="form-check-input" type="checkbox" data-name="visible" value="1" checked />
                                <span class="form-check-label">{{ __('dashboard.Visible') }}</span>
                            </label>
                        </div>
                    @endif
                    <div class="col-12 text-end">
                        <button type="button" class="btn btn-outline-danger btn-sm section-repeatable-remove">
                            {{ __('dashboard.Delete') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
