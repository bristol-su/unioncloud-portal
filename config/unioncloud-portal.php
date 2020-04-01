<?php

return [
    'enabled' => [
        'data-users' => env('USE_UNIONCLOUD_DATA_USERS', false),
        'memberships' => env('USE_UNIONCLOUD_MEMBERSHIPS', false),
    ],
    'users_per_minute' => env('UNIONCLOUD_CACHE_RATE', 20)
];
