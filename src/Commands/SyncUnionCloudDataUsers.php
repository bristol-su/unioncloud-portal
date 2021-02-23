<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\UnionCloud\Events\UserRetrieved;
use BristolSU\UnionCloud\Jobs\RetrieveUsers;
use Illuminate\Console\Command;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;


class SyncUnionCloudDataUsers extends Command
{
    const RECORDS_PER_PAGE = 500;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:sync:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Syncronise all users from UnionCloud.';

    /**
     * Execute the console command.
     * @param UnionCloud $unionCloud
     * @throws \Exception
     */
    public function handle(UnionCloud $unionCloud)
    {
        $requestRate = config('unioncloud-portal.user_requests_rate', 10);

        $unionCloudResponse = $unionCloud->getAllUsers(1, static::RECORDS_PER_PAGE);

        $totalPages = $unionCloudResponse->getTotalPages();

        $delay = ceil(60/($requestRate * 0.8));

        $users = $unionCloudResponse->get();

        foreach ($users as $user) {
            UserRetrieved::dispatch($user);
        }

        RetrieveUsers::dispatch(2, $totalPages, $delay);
    }
}
