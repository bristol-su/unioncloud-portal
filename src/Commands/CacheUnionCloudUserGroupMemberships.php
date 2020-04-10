<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Models\GroupGroupMembership;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use BristolSU\UnionCloud\UnionCloud\UnionCloudCacher;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository;
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
    protected $description = 'Cache group memberships from UnionCloud';

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

        while($this->idStore->count() > 0 && $completed <= config('unioncloud-portal.user_groups_per_minute') && !$failed) {
            try {
                $id = $this->idStore->pop();
                $this->line('Caching user group #' . $id);
                $this->updateCache($id, function($id) {
                    return $this->repository->getUserGroupMemberships($id);
                });
                $completed += 1;
            } catch (\Exception $e) {
                $this->error('Failed caching user group #' . $id);
                if($e instanceof ClientException && $e->getCode() === 429) {
                    $this->idStore->push($id);
                    $failed = true;
                } elseif($e instanceof ModelNotFoundException) {
                    Log::info(sprintf('Unioncloud usergroup %s not found', $id));
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
        $ids = GroupGroupMembership::all()->pluck('usergroup_id');
        $this->idStore->setIds($ids);
    }

    private function updateCache($id, \Closure $callback)
    {
        $key = 'unioncloud-user-group-get-by-id:' . $id;
        $hasCache = Cache::has($key);
        if($hasCache) {
            $value = Cache::get($key);
            Cache::forget($key);
        }
        try {
            $callback($id);
        } catch (\Exception $e) {
            if($hasCache) {
                Cache::forever($key, $value);
            }
            throw $e;
        }
    }

}
