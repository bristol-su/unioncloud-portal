<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Cache\DataUser;
use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Implementations\DataUserRepository;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheUnionCloudDataUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache users from UnionCloud';

    /**
     * @var IdStore
     */
    private $idStore;
    /**
     * @var UnionCloud
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
        $this->repository = app(DataUserRepository::class);
    }
 
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $completed = 0;
        $failed = false;
        
        while($this->idStore->count() > 0 && $completed <= config('unioncloud-portal.users_per_minute') && !$failed) {
            try {
                $id = $this->idStore->pop();
                $this->line('Caching user #' . $id);
                $this->updateCache($id, function($id) {
                    return $this->repository->getById($id);
                });
                $completed += 1;
            } catch (\Exception $e) {
                $this->error('Failed caching user #' . $id);
                if($e instanceof ClientException && $e->getCode() === 429) {
                    $this->idStore->push($id);
                    $failed = true;
                } elseif($e instanceof ModelNotFoundException) {
                    Log::info(sprintf('Unioncloud user %s not found', $id));
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
            return $user->id();
        });
        $this->idStore->setIds($ids);
    }

    private function updateCache($id, \Closure $callback)
    {
        $key = DataUser::class . '@getByID:' . $id;
        $hasCache = Cache::has($key);
        if($hasCache) {
            $value = Cache::get($key);
            Cache::forget($key);
        }
        try {
            $newValue = $callback($id);
        } catch (\Exception $e) {
            if($hasCache) {
                Cache::forever($key, $value);
            }
            throw $e;
        }
        Cache::forever($key, $newValue);
    }

}
