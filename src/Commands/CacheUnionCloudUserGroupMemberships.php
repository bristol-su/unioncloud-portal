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

class CacheUnionCloudUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:members:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Work through queue of memberships to cache.';

    /**
     * @var IdStore
     */
    private IdStore $idStore;
    /**
     * @var UserGroup
     */
    private UserGroup $repository;

    /**
     * Create a new command instance.
     *
     * @param IdStore $idStore
     */
    public function __construct(IdStore $idStore)
    {
        parent::__construct();
        $this->idStore = $idStore;
        $this->repository = app(UserGroup::class);
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->prepareIdStore();

        $completed = 0;
        $failed = false;

        while(
            $this->idStore->count() > 0 &&
            $completed < config('unioncloud-portal.user_groups_per_minute') &&
            !$failed) {

            $controlGroupId = $this->idStore->pop();
            $this->line('Caching memberships for group #' . $controlGroupId);

            try {
                $controlGroup = app(Group::class)->getById($controlGroupId);
                $users = $this->repository->getUsersThroughGroup($controlGroup);

                UsersWithMembershipToGroupRetrieved::dispatch($controlGroup, $users->toArray());

                $completed += 1;
            } catch (\Exception $e) {
                $this->error('Failed caching memberships for group #' . $controlGroupId);
                if($e instanceof ClientException && $e->getCode() === 429) {
                    $this->idStore->push($controlGroupId);
                    $failed = true;
                } elseif($e instanceof ModelNotFoundException) {
                    Log::info(sprintf('Members for control group %s not found', $controlGroupId));
                } else {
                    throw $e;
                }
            }
        }

        $this->info(sprintf('Cached %d users and %s', $completed, ($failed?'failed':'succeeded')));

    }

    private function prepareIdStore()
    {
        if($this->idStore->count() === 0) {
            $this->line('Refreshing group queue');
            $controlGroupIds = app(Group::class)->all()->map(function($group) {
                return $group->id();
            });
            $this->idStore->setIds($controlGroupIds);
        }
    }

}
