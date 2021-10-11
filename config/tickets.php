<?php

return [
    'user' => \App\Models\User::class,
    'from_address' => 'support@uplaylist.com',
    'from_name' => 'uPlaylist Support',
    'routes' => [
        'api' => [
            'prefix' => 'api/v1',
            'middleware' => [],
        ],
        'web' => [
            'domain' => 'support.uplaylist.com',
            'prefix' => '',
            'middleware' => ['web'],
        ],
    ],
    'created' => [
        'email' => null,
        'user_guard' => 'api',
        'callback' => null,
    ],
];
