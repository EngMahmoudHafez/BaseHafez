<?php

// Custom Configuration Settings
// -------------------------------------------------------------------------------------
// IMPORTANT: Ensure that you clear your browser's local storage to properly view the configuration changes in the template.
// To clear local storage, follow this guide: (https://www.leadshook.com/help/how-to-clear-local-storage-in-google-chrome-browser/).

return [
    'custom' => [
        'myLayout' => 'vertical', // Layout type: 'vertical' (default), 'horizontal'
        // 'primaryColor' => '#FF4646', // Set the primary color
        'myTheme' => 'light', // Theme options: 'light' (default), 'dark', 'system'
        'mySkins' => 'default', // Skin options: 'default', 'bordered'
        'hasSemiDark' => false, // Semi-dark mode: true/false (false by default)
        'myRTLMode' => false, // Right-to-left (RTL) layout: true/false (false by default)
        'hasCustomizer' => true, // Enable/Disable customizer: true (default) or false
        'displayCustomizer' => true, // Display customizer UI: true (default) or false
        'contentLayout' => 'wide', // Layout size: 'compact' (container-xxl) or 'wide' (container-fluid)
        'navbarType' => 'sticky', // Navbar type: 'sticky', 'static', or 'hidden' (only for vertical layout)
        'footerFixed' => false, // Footer fixed position: true/false (false by default)
        'menuFixed' => true, // Menu fixed position: true (default) or false (only for vertical layout)
        'menuCollapsed' => false, // Menu collapsed state: true/false (false by default)
        'headerType' => 'fixed', // Header type: 'static' or 'fixed' (only for horizontal layout)
        'showDropdownOnHover' => true, // Dropdown on hover for horizontal layout: true/false
        // Only controls that don't depend on the Vuexy preview SVGs under
        // public/img/customizer (which this base does not ship). These three
        // need no images: colour picker, light/dark/system theme, semi-dark menu.
        'customizerControls' => [
            'color',
            'theme',
            'semiDark',
        ],
    ],
];
