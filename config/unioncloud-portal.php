<?php

return [
    'enabled' => [
        'data-users' => env('USE_UNIONCLOUD_DATA_USERS', false),
        'memberships' => env('USE_UNIONCLOUD_MEMBERSHIPS', false),
    ],
    'users_per_minute' => env('UNIONCLOUD_USER_CACHE_RATE', 20),
    'user_groups_per_minute' => env('UNIONCLOUD_USERGROUP_CACHE_RATE', 20),
    'user_user_groups_per_minute' => env('UNIONCLOUD_USER_USERGROUP_CACHE_RATE', 20)
];
