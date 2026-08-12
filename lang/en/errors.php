<?php

return [
    'back_home' => 'Back to home',

    // Generic API error messages (used by the exception handler).
    'unauthenticated' => 'Unauthenticated. Please sign in.',
    'forbidden' => 'You are not authorized to perform this action.',
    'not_found' => 'The requested resource was not found.',
    'server_error' => 'Something went wrong. Please try again later.',

    // Error pages.
    '403' => [
        'heading' => 'Access denied',
        'message' => 'You do not have permission to view this page.',
    ],
    '404' => [
        'heading' => 'Page not found',
        'message' => 'The page you are looking for does not exist or has been moved.',
    ],
    '419' => [
        'heading' => 'Page expired',
        'message' => 'Your session has expired. Please refresh the page and try again.',
    ],
    '429' => [
        'heading' => 'Too many requests',
        'message' => 'You have made too many requests. Please slow down and try again shortly.',
    ],
    '500' => [
        'heading' => 'Server error',
        'message' => 'Something went wrong on our end. Please try again later.',
    ],
    '503' => [
        'heading' => 'Under maintenance',
        'message' => 'We are performing scheduled maintenance. We will be back shortly.',
    ],
];
