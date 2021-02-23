<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\UnionCloud\Events\UserRetrieved;
use Illuminate\Support\Facades\Log;

class LogDataUserRetrieval
{

    /**
     * Execute the console command.
     */
    public function handle(UserRetrieved $userRetrieved)
    {
        Log::info('Found user with uid of ' . $userRetrieved->unionCloudUser->uid);
    }

}
