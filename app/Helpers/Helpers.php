<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Config;

final class Helpers
{
    private const DEFAULTS = [
        'myLayout' => 'vertical',
        'myTheme' => 'light',
        'mySkins' => 'default',
        'hasSemiDark' => false,
        'myRTLMode' => false,
        'hasCustomizer' => false,
        'showDropdownOnHover' => true,
        'displayCustomizer' => false,
        'contentLayout' => 'wide',
        'headerType' => 'fixed',
        'navbarType' => 'sticky',
        'menuFixed' => true,
        'menuCollapsed' => false,
        'footerFixed' => false,
        'customizerControls' => [],
    ];

    private const ALLOWED_VALUES = [
        'myLayout' => ['vertical', 'horizontal', 'blank', 'front'],
        'myTheme' => ['light', 'dark', 'system'],
        'mySkins' => ['default', 'bordered', 'raspberry'],
        'contentLayout' => ['compact', 'wide'],
        'headerType' => ['fixed', 'static'],
        'navbarType' => ['sticky', 'static', 'hidden'],
    ];

    /**
     * @return array<string, string>
     */
    public static function getMenuAttributes(bool $semiDarkEnabled): array
    {
        return $semiDarkEnabled ? ['data-bs-theme' => 'dark'] : [];
    }

    /**
     * @return array<string, mixed>
     */
    public static function appClasses(): array
    {
        $settings = self::normalizedSettings();
        $layout = $settings['myLayout'];
        $isAdmin = $layout !== 'front';
        $cookiePrefix = $isAdmin ? 'admin' : 'front';

        $themePreference = self::allowedCookie(
            "{$cookiePrefix}-mode",
            self::ALLOWED_VALUES['myTheme'],
            $settings['myTheme'],
        );
        $theme = $themePreference === 'system'
            ? self::allowedCookie("{$cookiePrefix}-colorPref", ['light', 'dark'], 'light')
            : $themePreference;

        $skinName = $isAdmin
            ? self::allowedCookie('customize_skin', self::ALLOWED_VALUES['mySkins'], $settings['mySkins'])
            : 'default';
        $semiDark = $isAdmin
            ? self::booleanCookie('customize_semi_dark', $settings['hasSemiDark'])
            : false;
        $primaryColor = self::hexColor(
            self::rawCookie("{$cookiePrefix}-primaryColor")
                ?? config('custom.custom.primaryColor'),
        );

        return [
            'layout' => $layout,
            'skins' => $settings['mySkins'],
            'skinName' => $skinName,
            'semiDark' => $semiDark,
            'color' => $primaryColor,
            'theme' => $theme,
            'themeOpt' => $settings['myTheme'],
            'themeOptVal' => $themePreference,
            'rtlMode' => app()->isLocale('ar') ? 'rtl' : 'ltr',
            'textDirection' => app()->isLocale('ar') ? 'rtl' : 'ltr',
            'menuCollapsed' => self::booleanCookie('LayoutCollapsed', $settings['menuCollapsed'])
                ? 'layout-menu-collapsed'
                : '',
            'hasCustomizer' => $settings['hasCustomizer'],
            'showDropdownOnHover' => $settings['showDropdownOnHover'],
            'displayCustomizer' => $settings['displayCustomizer'],
            'contentLayout' => self::allowedCookie(
                'contentLayout',
                self::ALLOWED_VALUES['contentLayout'],
                $settings['contentLayout'],
            ),
            'headerType' => self::allowedCookie(
                'headerType',
                self::ALLOWED_VALUES['headerType'],
                $settings['headerType'],
            ) === 'fixed' ? 'layout-menu-fixed' : '',
            'navbarType' => match (self::allowedCookie(
                'navbarType',
                self::ALLOWED_VALUES['navbarType'],
                $settings['navbarType'],
            )) {
                'sticky' => 'layout-navbar-fixed',
                'hidden' => 'layout-navbar-hidden',
                default => '',
            },
            'menuFixed' => $settings['menuFixed'] ? 'layout-menu-fixed' : '',
            'footerFixed' => $settings['footerFixed'] ? 'layout-footer-fixed' : '',
            'customizerControls' => $settings['customizerControls'],
            'menuAttributes' => self::getMenuAttributes($semiDark),
        ];
    }

    /**
     * @param  array<string, mixed>  $pageConfigs
     */
    public static function updatePageConfig(array $pageConfigs): void
    {
        foreach ($pageConfigs as $config => $value) {
            Config::set("custom.custom.{$config}", $value);
        }
    }

    public static function generatePrimaryColorCSS(?string $color): string
    {
        $color = self::hexColor($color);

        if ($color === null) {
            return '';
        }

        $red = hexdec(substr($color, 1, 2));
        $green = hexdec(substr($color, 3, 2));
        $blue = hexdec(substr($color, 5, 2));
        $yiq = (($red * 299) + ($green * 587) + ($blue * 114)) / 1000;
        $contrastColor = $yiq >= 150 ? '#000' : '#fff';

        return <<<CSS
:root, [data-bs-theme=light], [data-bs-theme=dark] {
  --bs-primary: {$color};
  --bs-primary-rgb: {$red}, {$green}, {$blue};
  --bs-primary-bg-subtle: rgba({$red}, {$green}, {$blue}, 0.1);
  --bs-primary-border-subtle: rgba({$red}, {$green}, {$blue}, 0.3);
  --bs-primary-contrast: {$contrastColor};
}
CSS;
    }

    /**
     * @return array<string, mixed>
     */
    private static function normalizedSettings(): array
    {
        $configured = config('custom.custom', []);
        $settings = array_replace(self::DEFAULTS, is_array($configured) ? $configured : []);

        foreach (self::DEFAULTS as $key => $default) {
            if (get_debug_type($settings[$key]) !== get_debug_type($default)) {
                $settings[$key] = $default;
            }
        }

        foreach (self::ALLOWED_VALUES as $key => $allowedValues) {
            if (! in_array($settings[$key], $allowedValues, true)) {
                $settings[$key] = self::DEFAULTS[$key];
            }
        }

        return $settings;
    }

    private static function allowedCookie(string $name, array $allowedValues, string $fallback): string
    {
        $value = self::rawCookie($name);

        return $value !== null && in_array($value, $allowedValues, true)
            ? $value
            : $fallback;
    }

    private static function booleanCookie(string $name, bool $fallback): bool
    {
        $value = self::rawCookie($name);

        if ($value === null) {
            return $fallback;
        }

        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $fallback;
    }

    private static function rawCookie(string $name): ?string
    {
        $value = $_COOKIE[$name] ?? null;

        return is_string($value) ? $value : null;
    }

    private static function hexColor(mixed $color): ?string
    {
        if (! is_string($color) || preg_match('/^#[0-9a-f]{6}$/i', $color) !== 1) {
            return null;
        }

        return strtolower($color);
    }
}
