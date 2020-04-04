<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FindCachedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:cached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'See all cached users from UnionCloud';
    /**
     * @var IdStore
     */
    private $idStore;

    /**
     * Create a new command instance.
     *
     * @param IdStore $idStore
     */
    public function __construct(IdStore $idStore)
    {
        parent::__construct();
        $this->idStore = $idStore;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cached = $this->cached();
        $total = $this->total();
        $remaining = $this->inQueue();

        $this->line(
            sprintf('We\'ve cached %d%% of the users (%d/%d) and have %d left to cache.', ($cached / $total) * 100, $cached, $total, $remaining)
        );
    }

    public function inQueue()
    {
        return $this->idStore->count();
    }
    
    public function cached()
    {
        return app(UserRepository::class)->all()->map(function (User $user) {
            return $user->dataProviderId();
        })->filter(function (int $id) {
            return Cache::has('unioncloud-data-user-get-by-id:' . $id);
        })->count();
    }

    public function total()
    {
        return app(UserRepository::class)->all()->map(function (User $user) {
            return $user->dataProviderId();
        })->count();
    }
}
