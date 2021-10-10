<?php

return [
    'user' => \App\Models\User::class,
    'from_address' => 'support@uplaylist.com',
    'from_name' => 'uPlaylist Support',
    'ticket_create_user_guard' => 'api',
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
];
