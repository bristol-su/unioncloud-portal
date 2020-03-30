<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Implementations\DataUserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class CacheUnionCloudDataUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:cacheusers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache users from UnionCloud';

    protected static $usersToCache = 10;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        for($i = 0; $i <= static::$usersToCache; $i++) {
            app(DataUserRepository::class)->getById($id = $this->getId());
            $this->line(sprintf('Cached user #%s', $id));
        }
    }

    private function getId()
    {
        if(Cache::get('uc-ids-to-cache', collect())->count() === 0) {
            Cache::forever('uc-ids-to-cache', app(UserRepository::class)->all()->map(function(User $user) {
                return $user->id();
            }));
        }

        /** @var Collection $ids */
        $ids = Cache::get('uc-ids-to-cache', collect());
        $id = $ids->shift();
        Cache::forever('uc-ids-to-cache', $ids);
        return $id;
    }
}
