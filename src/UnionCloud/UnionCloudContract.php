<?php

namespace BristolSU\UnionCloud\UnionCloud;

use Illuminate\Support\Collection;
use Twigger\UnionCloud\API\Resource\User;
use Twigger\UnionCloud\API\Resource\UserGroupMembership;
use Twigger\UnionCloud\API\Response\UserResponse;

interface UnionCloudContract
{

    /**
     * Get all user group memberships from a user group
     *
     * @param int $userGroupId
     * @return Collection|UserGroupMembership[]
     *
     * @throw ModelNotFoundException
     */
    public function getUserGroupMemberships(int $userGroupId): Collection;

    /**
     * Get all user group memberships for a given user
     *
     * @param int $userId
     * @return Collection|UserGroupMembership[]
     */
    public function getUsersUserGroupMemberships(int $userId): Collection;

    public function getUserById(int $id): User;

    public function searchForUser(array $attributes);

    public function searchForUsers(array $attributes);

    public function getAllUsers(int $page, int $perPage): UserResponse;
}
