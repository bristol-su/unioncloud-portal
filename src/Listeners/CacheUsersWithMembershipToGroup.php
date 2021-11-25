<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\Pivots\UserGroup;
use BristolSU\ControlDB\Contracts\Models\DataUser;
use BristolSU\ControlDB\Events\Pivots\UserGroup\UserAddedToGroup;
use BristolSU\ControlDB\Events\Pivots\UserGroup\UserRemovedFromGroup;
use BristolSU\ControlDB\Contracts\Repositories\User;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use Illuminate\Contracts\Cache\Repository;

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
        $key = sprintf('%s@getUsersThroughGroup:%s', UserGroup::class, $event->group->id());
        if($this->cache->has($key)) {
            $ids = collect($this->cache->get($key));
            $newIds = collect($event->unionCloudUsers);
            $updatingIds = $newIds->diff($ids);
            $removingIds = $ids->diff($newIds);
            $updatingIds->each(fn(int $id) => event(new UserAddedToGroup(
                app(User::class)->getById($id),
                $event->group
            )));
            $removingIds->each(fn(int $id) => event(new UserRemovedFromGroup(
                app(User::class)->getById($id),
                $event->group
            )));
        }
        $this->cache->forever(
            $key,
            collect($event->unionCloudUsers)->map(fn(\BristolSU\ControlDB\Contracts\Models\User $user) => $user->id())->all()
        );
    }

}
