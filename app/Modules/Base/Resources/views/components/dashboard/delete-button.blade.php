@props([
    'modalId' => null,
    'itemName' => '',
    'itemNameEn' => null,
    'deleteRoute' => '#',
    'warningMessage' => null,
    'hasChildren' => false,
    'childrenCount' => 0,
    'size' => 'sm', // sm, md, lg
])

@php
    $modalId = $modalId ?? 'deleteModal' . uniqid();

    $sizeMap = [
        'sm' => 'btn-sm',
        'md' => '',
        'lg' => 'btn-lg',
    ];
    $sizeKey = is_string($size) ? $size : 'sm';
    $sizeClass = $sizeMap[$sizeKey] ?? $sizeMap['sm'];

    $classes = 'btn btn-icon btn-danger table-action-btn ' . $sizeClass;
@endphp

{{-- Delete Button --}}
<button
    type="button"
    {{ $attributes->merge(['class' => $classes]) }}
    data-bs-toggle="modal"
    data-bs-target="#{{ $modalId }}"
    title="{{ __('dashboard.delete') }}"
    aria-label="{{ __('dashboard.delete') }}"
>
    <span class="dashboard-btn-icon" aria-hidden="true">
        <i class="{{ dashboard_icon_class('trash') }}"></i>
    </span>
</button>

{{-- Delete Modal --}}
<div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger pb-2">
                <h5 class="modal-title text-white">
                    <i class="{{ dashboard_icon_class('alert-triangle') }} me-2"></i>
                    {{ __('dashboard.confirm_delete') }}
                </h5>
                <button
                    type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="{{ __('dashboard.close') }}"
                ></button>
            </div>
            <form action="{{ $deleteRoute }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-body">
                    <div class="mb-3 text-center">
                        <i class="{{ dashboard_icon_class('alert-circle') }} text-warning" style="font-size: 60px"></i>
                    </div>
                    <h4 class="mb-3 text-center">{{ __('dashboard.delete_warning') }}</h4>
                    <p class="mb-2 text-center">{{ $warningMessage ?? __('dashboard.delete_confirmation') }}</p>
                    <div class="alert alert-light text-dark border text-center">
                        <strong>{{ $itemName }}</strong>
                    </div>
                    @if ($hasChildren && $childrenCount > 0)
                        <div class="alert alert-warning">
                            <i class="{{ dashboard_icon_class('alert-triangle') }} me-2"></i>
                            {{ __('dashboard.has_children_warning', ['count' => $childrenCount]) }}
                        </div>
                    @endif

                    {{ $slot }}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                        <i class="{{ dashboard_icon_class('x') }} me-1"></i>
                        {{ __('dashboard.cancel') }}
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="{{ dashboard_icon_class('trash-2') }} me-1"></i>
                        {{ __('dashboard.delete') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
