<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\Pivots\UserGroup;
use BristolSU\ControlDB\Contracts\Models\Group;
use BristolSU\UnionCloud\Events\UsersMembershipsRetrieved;
use Illuminate\Contracts\Cache\Repository;

class CacheUsersMemberships
{

    /**
     * @var Repository
     */
    private Repository $cache;

    public function __construct(Repository $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Execute the console command.
     */
    public function handle(UsersMembershipsRetrieved $event)
    {
        $this->cache->forever(
            sprintf('%s@getGroupsThroughUser:%s', UserGroup::class, $event->user->id()),
            $event->groups->map(fn(Group $group) => $group->id())->all()
        );
    }

}
