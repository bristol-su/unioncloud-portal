<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Repositories\Group;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use BristolSU\UnionCloud\Implementations\UserGroup;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheUnionCloudUserGroupMembershipsForSingleGroup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:members:cache-group {groupid}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work through queue of memberships to cache.';

    /**
     * @var UserGroup
     */
    private UserGroup $repository;

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
        $this->repository = app(UserGroup::class);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $controlGroupId = $this->argument('groupid');
        $this->line('Caching memberships for group #' . $controlGroupId);

        try {
            $controlGroup = app(Group::class)->getById($controlGroupId);
            $users = $this->repository->getUsersThroughGroup($controlGroup);

            UsersWithMembershipToGroupRetrieved::dispatch($controlGroup, $users);

        } catch (\Exception $e) {
            $this->error('Failed caching memberships for group #' . $controlGroupId);
            if($e instanceof ClientException && $e->getCode() === 429) {
                $this->line('Failed with a 429 error');
            } elseif($e instanceof ModelNotFoundException) {
                Log::info(sprintf('Members for control group %s not found', $controlGroupId));
            } else {
                throw $e;
            }
        }

    }

}
