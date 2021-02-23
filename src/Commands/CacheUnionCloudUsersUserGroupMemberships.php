<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Events\UsersMembershipsRetrieved;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use BristolSU\UnionCloud\Implementations\UserGroup;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class CacheUnionCloudUsersUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:usermemberships:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache users memberships from UnionCloud';

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
            $completed < config('unioncloud-portal.user_user_groups_per_minute') &&
            !$failed) {

            $controlUserId = $this->idStore->pop();
            $this->line('Caching memberships for user #' . $controlUserId);

            try {
                $controlUser = app(UserRepository::class)->getById($controlUserId);
                $groups = $this->repository->getGroupsThroughUser($controlUser);

                UsersMembershipsRetrieved::dispatch($controlUser, $groups);

                $completed += 1;
            } catch (\Exception $e) {
                $this->error('Failed caching user memberships #' . $controlUserId);
                if($e instanceof ClientException && $e->getCode() === 429) {
                    $this->idStore->push($controlUserId);
                    $failed = true;
                } elseif($e instanceof ModelNotFoundException) {
                    Log::info(sprintf('Memberships for user %s not found', $controlUserId));
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
            $this->line('Refreshing user queue');
            $controlUserIds = app(UserRepository::class)->all()->map(function(User $user) {
                return $user->id();
            });
            $this->idStore->setIds($controlUserIds);
        }
    }
}
