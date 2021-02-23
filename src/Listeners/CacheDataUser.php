<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\DataUser;
use BristolSU\UnionCloud\Events\UserRetrieved;
use BristolSU\UnionCloud\Models\DataUserModel;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Cache;

class CacheDataUser
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
    public function handle(UserRetrieved $userRetrieved)
    {
        $this->cache->forever(
            sprintf('%s@getByID:%s', DataUser::class, $userRetrieved->unionCloudUser->uid),
            DataUserModel::fromUnionCloudUser($userRetrieved->unionCloudUser)
        );
    }

}
