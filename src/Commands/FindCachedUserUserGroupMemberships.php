<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FindCachedUserUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:usermemberships:cached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'See all cached users group memberships from UnionCloud';
    /**
     * @var IdStore
     */
    private $idStore;
    
    private $userIds;

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
            sprintf('We\'ve cached %d%% of the memberships of users (%d/%d) and have %d left to cache.', ($cached / $total) * 100, $cached, $total, $remaining)
        );
    }

    public function inQueue()
    {
        return $this->idStore->count();
    }

    public function cached()
    {
        return $this->userIds()->filter(function (int $id) {
            return Cache::has('unioncloud-user-group-ugm-through-user:' . $id);
        })->count();
    }

    public function total()
    {
        return $this->userIds()->count();
    }

    public function userIds()
    {
        if(!$this->userIds) {
            $this->userIds = app(UserRepository::class)->all()->map(function (User $user) {
                return $user->dataProviderId();
            });
        }
        return $this->userIds;
    }
}
