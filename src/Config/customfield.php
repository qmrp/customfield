<?php

return [
    'cache' => [
        'enabled' => env('CUSTOMFIELD_CACHE_ENABLED', true),
        'ttl' => env('CUSTOMFIELD_CACHE_TTL', 3600),
    ],

    'permissions' => [
        'enabled' => env('CUSTOMFIELD_PERMISSIONS_ENABLED', true),
        'default_permissions' => [
            'view' => true,
            'edit' => true,
            'delete' => false,
        ],
    ],

    'middleware' => [
        'auth' => env('CUSTOMFIELD_AUTH_MIDDLEWARE', 'auth:sanctum'),
        'permission' => \Qmrp\CustomField\Http\Middleware\CustomFieldPermission::class,
    ],

    'routes' => [
        'prefix' => env('CUSTOMFIELD_ROUTE_PREFIX', 'api/customfield'),
        'middleware' => env('CUSTOMFIELD_ROUTE_MIDDLEWARE', 'api'),
    ],
];
