<?php

if (! function_exists('array_merge_recursive_distinct')) {
    /**
     * @param  array<string, mixed>  $base
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    function array_merge_recursive_distinct(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            $base[$key] = is_array($value) && isset($base[$key]) && is_array($base[$key])
                ? array_merge_recursive_distinct($base[$key], $value)
                : $value;
        }

        return $base;
    }
}

if (! function_exists('has_permission')) {
    function has_permission(string $permission): bool
    {
        $manager = auth('manager')->user();

        return $manager !== null && $manager->hasPermission($permission);
    }
}

if (! function_exists('dashboard_icon_class')) {
    function dashboard_icon_class(?string $icon): string
    {
        $icon = trim((string) $icon);

        if ($icon === '') {
            return 'ti ti-help-circle';
        }

        if (str_contains($icon, 'ti-') || str_contains($icon, 'fa-')) {
            return $icon;
        }

        $iconKey = str_replace(['feather ', 'icon-'], '', $icon);
        $map = [
            'alert-circle' => 'alert-circle',
            'alert-triangle' => 'alert-triangle',
            'check-circle' => 'circle-check',
            'loader' => 'loader-2 spin',
            'menu' => 'menu-2',
            'more-vertical' => 'dots-vertical',
            'save' => 'device-floppy',
            'trash-2' => 'trash',
            'x-circle' => 'circle-x',
        ];

        return 'ti ti-' . ($map[$iconKey] ?? $iconKey);
    }
}
