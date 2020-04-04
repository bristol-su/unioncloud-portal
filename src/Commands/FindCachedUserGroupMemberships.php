<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Models\GroupGroupMembership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class FindCachedUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:members:cached';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'See all cached group memberships from UnionCloud';
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
            sprintf('We\'ve cached %d%% of the usergroup memberships (%d/%d) and have %d left to cache.', ($cached / $total) * 100, $cached, $total, $remaining)
        );
    }

    public function inQueue()
    {
        return $this->idStore->count();
    }

    public function cached()
    {
        return GroupGroupMembership::all()->pluck('usergroup_id')->filter(function (int $id) {
            return Cache::has('unioncloud-user-group-get-by-id:' . $id);
        })->count();
    }

    public function total()
    {
        return GroupGroupMembership::all()->pluck('usergroup_id')->count();
    }
}
