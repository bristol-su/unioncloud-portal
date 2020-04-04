<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Jobs\CacheUser;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use BristolSU\UnionCloud\UnionCloud\UnionCloudCacher;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

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
    private $idStore;
    /**
     * @var UnionCloudCacher
     */
    private $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(IdStore $idStore)
    {
        parent::__construct();
        $this->idStore = $idStore;
        $this->repository = new UnionCloudCacher(
            new UnionCloud(app(\Twigger\UnionCloud\API\UnionCloud::class)),
            app(Repository::class)
        );
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $completed = 0;
        $failed = false;

        while($this->idStore->count() > 0 && $completed <= config('unioncloud-portal.user_user_groups_per_minute') && !$failed) {
            try {
                $id = $this->idStore->pop();
                $this->line('Caching user #' . $id);
                $this->updateCache($id, function($id) {
                    return $this->repository->getUsersUserGroupMemberships($id);
                });
                $completed += 1;
            } catch (\Exception $e) {
                $this->error('Failed caching user #' . $id);
                if($e instanceof ClientException && ($e->getCode() === 401 || $e->getCode() === 403)) {
                    $this->idStore->push($id);
                    $failed = true;
                } else {
                    throw $e;
                }
            }
        }

        $this->info(sprintf('Cached %d users and %s', $completed, ($failed?'failed':'succeeded')));

        if($this->idStore->count() === 0) {
            $this->info('Refreshing cache store');
            $this->refreshIdStore();
        }
    }

    private function refreshIdStore()
    {
        $ids = app(UserRepository::class)->all()->map(function(User $user) {
            return $user->dataProviderId();
        });
        $uncachedIds = $ids->filter(function(int $id) {
            return Cache::missing('unioncloud-user-group-ugm-through-user:' . $id);
        });

        if(($ids->count() - $uncachedIds->count()) + $this->idStore->count() === $ids) {
            $this->idStore->setIds($uncachedIds);
        } else {
            $this->idStore->setIds($ids);
        }
    }

    private function updateCache($id, \Closure $callback)
    {
        $key = 'unioncloud-user-group-ugm-through-user:' . $id;
        $hasCache = Cache::has($key);
        if($hasCache) {
            $value = Cache::get($key);
            Cache::forget($key);
        }
        try {
            $callback($id);
        } catch (\Exception $e) {
            if($hasCache) {
                Cache::put($key, $value, UnionCloudCacher::$cacheFor);
            }
            throw $e;
        }
    }

}
