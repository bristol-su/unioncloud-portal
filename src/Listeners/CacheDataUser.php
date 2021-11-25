<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Cache\DataUser;
use BristolSU\ControlDB\Events\DataUser\DataUserUpdated;
use BristolSU\ControlDB\Events\DataUser\DataUserCreated;
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
        $unionCloudDataUser = DataUserModel::fromUnionCloudUser($userRetrieved->unionCloudUser);

        $key = sprintf('%s@getById:%s', DataUser::class, $userRetrieved->unionCloudUser->uid);
        if($this->cache->has($key)) {
            $dataUser = $this->cache->get($key);
            $updatedData = array_diff($dataUser->toArray(), $unionCloudDataUser->toArray());
            if(count($updatedData) > 0) {
                event(new DataUserUpdated($unionCloudDataUser, $updatedData));
            }
        } else {
            DataUserCreated::dispatch($unionCloudDataUser);
        }

        $this->cache->forever(
            sprintf('%s@getById:%s', DataUser::class, $userRetrieved->unionCloudUser->uid),
            $unionCloudDataUser
        );
    }

}
