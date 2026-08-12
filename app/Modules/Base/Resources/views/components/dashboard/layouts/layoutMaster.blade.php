@isset($pageConfigs)
    @php
        Helper::updatePageConfig($pageConfigs);
    @endphp
@endisset
@php
    $configData = Helper::appClasses();

    $dashboardLayout = match ($configData['layout'] ?? null) {
        'horizontal' => 'base::components.dashboard.layouts.horizontalLayout',
        'blank' => 'base::components.dashboard.layouts.blankLayout',
        'front' => 'base::components.dashboard.layouts.layoutFront',
        default => 'base::components.dashboard.layouts.contentNavbarLayout',
    };
@endphp

@isset($configData['layout'])
    @include($dashboardLayout)
@endisset
