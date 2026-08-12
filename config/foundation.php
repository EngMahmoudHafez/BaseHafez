<?php

return [
    /*
    | The first Super Admin is bootstrapped from BASE_ADMIN_* ONLY. There are
    | deliberately NO fallback email/password values here: a missing variable
    | must yield null so ManagerSeeder skips creation rather than minting a
    | publicly-known account. See AGENTS.md §1.8.
    */
    'admin' => [
        'name' => env('BASE_ADMIN_NAME', 'Super Admin'),
        'email' => env('BASE_ADMIN_EMAIL'),
        'password' => env('BASE_ADMIN_PASSWORD'),
        'phone' => env('BASE_ADMIN_PHONE'),
    ],
];
