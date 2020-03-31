<?php

namespace BristolSU\UnionCloud\UnionCloud;

use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Collection;
use Twigger\UnionCloud\API\Resource\User;

class UnionCloudCacher implements UnionCloudContract
{

    /**
     * @var UnionCloudContract
     */
    private $unionCloudContract;
    /**
     * @var Repository
     */
    private $cache;
    
    static $cacheFor = 6000000;

    public function __construct(UnionCloudContract $unionCloudContract, Repository $cache)
    {
        $this->unionCloudContract = $unionCloudContract;
        $this->cache = $cache;
    }

    /**
     * @inheritDoc
     */
    public function getUserGroupMemberships(int $userGroupId): Collection
    {
        return $this->cache->remember('unioncloud-user-group-get-by-id:' . $userGroupId, static::$cacheFor, function() use ($userGroupId) {
            return $this->unionCloudContract->getUserGroupMemberships($userGroupId);
        });
    }

    /**
     * @inheritDoc
     */
    public function getUsersUserGroupMemberships(int $userId): Collection
    {
        return $this->cache->remember('unioncloud-user-group-ugm-through-user:' . $userId, static::$cacheFor, function() use ($userId) {
            return $this->unionCloudContract->getUsersUserGroupMemberships($userId);
        });
    }

    public function getUserById(int $id): User
    {
        return $this->cache->remember('unioncloud-data-user-get-by-id:' . $id, static::$cacheFor, function() use ($id) {
            return $this->unionCloudContract->getUserById($id);
        });
    }

    public function searchForUser(array $attributes)
    {
        return $this->cache->remember('unioncloud-data-user-search:' . json_encode($attributes), static::$cacheFor, function() use ($attributes) {
            return $this->unionCloudContract->searchForUser($attributes);
        });
    }
}