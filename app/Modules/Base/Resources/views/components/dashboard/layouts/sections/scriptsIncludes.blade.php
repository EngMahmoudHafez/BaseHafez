@php
    use Illuminate\Support\Facades\Vite;

    $menuCollapsed = $configData['menuCollapsed'] === 'layout-menu-collapsed' ? json_encode(true) : false;

    // Get skin value directly from the config, keeping it as numeric if applicable
    $skin = $configData['skins'] ?? 0;

    // If we have a skin name from cookie or other source, use that instead
    $skinName = $configData['skinName'] ?? '';

    // Use either the skin name or numeric ID, prioritizing the name if available
    $defaultSkin = $skinName ?: $skin;

    $primaryColor = $configData['color'] ?? null;
@endphp
<!-- laravel style -->
@vite(['resources/assets/vendor/js/helpers.js'])
<!-- beautify ignore:start -->
@if ($configData['hasCustomizer'])
    <!--! Template customizer & Theme config files MUST be included after core stylesheets and helpers.js in the <head> section -->
    <!--? Template customizer: To hide customizer set displayCustomizer value false in config.js.  -->
    @vite(['resources/assets/vendor/js/template-customizer.js'])
@endif

<!--? Config:  Mandatory theme config file contain global vars & default theme options, Set your preferred theme option in this file.  -->
@vite(['resources/assets/js/config.js'])

@if ($configData['hasCustomizer'])
    <script type="module">
        document.addEventListener('DOMContentLoaded', function() {
          // Initialize template customizer after DOM is loaded
          if (window.TemplateCustomizer) {
            try {
              // Get the skin currently applied to the document
              const appliedSkin = document.documentElement.getAttribute('data-skin') || "{{ $defaultSkin }}";

              window.templateCustomizer = new TemplateCustomizer({
                defaultTextDir: "{{ $configData['textDirection'] }}",
                @if ($primaryColor)
            defaultPrimaryColor: "{{ $primaryColor }}",
          @endif
                defaultTheme: "{{ $configData['themeOpt'] }}",
                defaultSkin: appliedSkin,
                defaultSemiDark: {{ $configData['semiDark'] ? 'true' : 'false' }},
                defaultShowDropdownOnHover: "{{ $configData['showDropdownOnHover'] }}",
                displayCustomizer: "{{ $configData['displayCustomizer'] }}",
                lang: '{{ app()->getLocale() }}',
                'controls': @js($configData['customizerControls']),
              });

              // Ensure color is applied on page load
              @if ($primaryColor)
          if (window.Helpers && typeof window.Helpers.setColor === 'function') {
            window.Helpers.setColor("{{ $primaryColor }}", true);
          }
        @endif
            } catch (error) {
              console.warn('Template customizer initialization error:', error);
            }
          }
        });
    </script>
    <style>
        /* The customizer injects tabler-* icons at runtime, which the build-time
           iconify generator never sees (they render as empty boxes). Draw them
           from inline SVG masks: the header reset/close buttons, the color-picker
           icon, and the three theme icons. */
        .template-customizer-themes-options i,
        .template-customizer-reset-btn i,
        .template-customizer-close-btn i,
        i.tabler-color-picker {
            display: inline-block;
            width: 1.375rem;
            height: 1.375rem;
            background-color: currentColor;
            -webkit-mask: center / contain no-repeat;
            mask: center / contain no-repeat;
        }
        .template-customizer-themes-options i::before,
        .template-customizer-reset-btn i::before,
        .template-customizer-close-btn i::before,
        i.tabler-color-picker::before {
            content: none;
        }
        .template-customizer-reset-btn i.tabler-refresh {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4'/%3E%3Cpath d='M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M20 11a8.1 8.1 0 0 0 -15.5 -2m-.5 -4v4h4'/%3E%3Cpath d='M4 13a8.1 8.1 0 0 0 15.5 2m.5 4v-4h-4'/%3E%3C/svg%3E");
        }
        .template-customizer-close-btn i.tabler-x {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M18 6l-12 12'/%3E%3Cpath d='M6 6l12 12'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M18 6l-12 12'/%3E%3Cpath d='M6 6l12 12'/%3E%3C/svg%3E");
        }
        i.tabler-color-picker {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M11 7l6 6'/%3E%3Cpath d='M4 16l11.7 -11.7a1 1 0 0 1 1.4 0l2.6 2.6a1 1 0 0 1 0 1.4l-11.7 11.7h-4v-4z'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M11 7l6 6'/%3E%3Cpath d='M4 16l11.7 -11.7a1 1 0 0 1 1.4 0l2.6 2.6a1 1 0 0 1 0 1.4l-11.7 11.7h-4v-4z'/%3E%3C/svg%3E");
        }
        .template-customizer-themes-options i.tabler-sun {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='4'/%3E%3Cpath d='M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='4'/%3E%3Cpath d='M3 12h1m8 -9v1m8 8h1m-9 8v1m-6.4 -15.4l.7 .7m12.1 -.7l-.7 .7m0 11.4l.7 .7m-12.1 -.7l-.7 .7'/%3E%3C/svg%3E");
        }
        .template-customizer-themes-options i.tabler-moon {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 3c.132 0 .263 0 .393 0a7.5 7.5 0 0 0 7.92 12.446a9 9 0 1 1 -8.313 -12.454z'/%3E%3C/svg%3E");
        }
        .template-customizer-themes-options i[class*="tabler-device-desktop"] {
            -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='12' rx='1'/%3E%3Cpath d='M7 20h10m-5 -4v4'/%3E%3C/svg%3E");
            mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23000' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Crect x='3' y='4' width='18' height='12' rx='1'/%3E%3Cpath d='M7 20h10m-5 -4v4'/%3E%3C/svg%3E");
        }
    </style>
@endif
