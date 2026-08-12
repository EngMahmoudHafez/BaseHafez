<?php

$appName = env('APP_NAME', 'Laravel Base');
$appUrl = env('APP_URL', 'http://localhost');
$authorUrl = env('APP_AUTHOR_URL', $appUrl);

return [
    'creatorName' => env('APP_AUTHOR', 'Your Team'),
    'creatorUrl' => $authorUrl,
    'templateName' => $appName,
    'templateSuffix' => 'Dashboard',
    'templateVersion' => '1.0.0',
    'templateFree' => false,
    'templateDescription' => env('APP_DESCRIPTION', 'Reusable Laravel modular application base'),
    'templateKeyword' => 'laravel, modular application, admin dashboard',
    'licenseUrl' => $appUrl,
    'livePreview' => $appUrl,
    'productPage' => $appUrl,
    'support' => $authorUrl,
    'moreThemes' => $authorUrl,
    'ogTitle' => $appName,
    'ogImage' => $appUrl . '/favicon.ico',
    'ogType' => 'website',
    'documentation' => $appUrl . '/docs',
    'generator' => '',
    'changelog' => $appUrl . '/changelog',
    'repository' => $authorUrl,
    'gitRepo' => '',
    'gitRepoAccess' => $authorUrl,
    'githubFreeUrl' => $authorUrl,
    'facebookUrl' => $authorUrl,
    'twitterUrl' => $authorUrl,
    'githubUrl' => $authorUrl,
    'dribbbleUrl' => $authorUrl,
    'instagramUrl' => $authorUrl,
];
