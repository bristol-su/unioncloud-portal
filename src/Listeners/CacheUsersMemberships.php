<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\Pivots\UserGroup;
use BristolSU\UnionCloud\Events\UsersMembershipsRetrieved;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
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
            $event->groups
        );
    }

}
