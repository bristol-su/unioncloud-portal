<?php

return [
    'enabled' => [
        'data-users' => env('USE_UNIONCLOUD_DATA_USERS', false),
        'memberships' => env('USE_UNIONCLOUD_MEMBERSHIPS', false),
    ],
    'user_groups_per_minute' => env('UNIONCLOUD_USERGROUP_CACHE_RATE', 20),
    /**
     * The maximum number of requests to the 'get all users' endpoint per minute.
     */
    'user_requests_rate' => env('UNIONCLOUD_USER_REQUEST_RATE', 10),

    'user_user_groups_per_minute' => env('UNIONCLOUD_USER_USERGROUP_CACHE_RATE', 20)
];
