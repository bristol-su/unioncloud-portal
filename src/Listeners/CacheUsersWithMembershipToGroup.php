<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\DataUser;
use BristolSU\ControlDB\Cache\Pivots\UserGroup;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use BristolSU\UnionCloud\Models\DataUserModel;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class CacheUsersWithMembershipToGroup
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
    public function handle(UsersWithMembershipToGroupRetrieved $event)
    {
        $this->cache->forever(
            sprintf('%s@getUsersThroughGroup:%s', UserGroup::class, $event->group->id()),
            collect($event->unionCloudUsers)
        );
    }

}
