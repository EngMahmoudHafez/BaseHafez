<?php

$administratorPermissions = [
    'dashboard' => 'v',
    'roles' => 'c,r,u,d',
    'managers' => 'c,r,u,d,e',
    'users' => 'c,r,u,d,e',
    'structures' => 'r,u',
    'contact-messages' => 'r,u,d',
    'notifications' => 'c,r,u,d',
];

return [
    'roles_structure' => [
        'super_admin' => $administratorPermissions,
        'admin' => $administratorPermissions,
        'support' => [
            'dashboard' => 'v',
            'users' => 'r',
            'contact-messages' => 'r,u',
            'notifications' => 'r',
        ],
    ],

    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
        'v' => 'view',
        'e' => 'export',
    ],
];
