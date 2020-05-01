<?php

namespace BristolSU\UnionCloud\Implementations;

use BristolSU\ControlDB\Contracts\Models\Group;
use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\UnionCloud\Exception\PermissionDeniedException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Twigger\UnionCloud\API\Resource\UserGroupMembership;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract as UnionCloud;
use BristolSU\UnionCloud\Models\GroupGroupMembership;
use BristolSU\UnionCloud\Models\DataUserModel;

class UserGroup implements \BristolSU\ControlDB\Contracts\Repositories\Pivots\UserGroup
{

    /**
     * @var UnionCloud
     */
    private $unionCloud;
    /**
     * @var \BristolSU\ControlDB\Contracts\Repositories\User
     */
    private $userRepository;
    /**
     * @var \BristolSU\ControlDB\Contracts\Repositories\Group
     */
    private $groupRepository;

    public function __construct(UnionCloud $unionCloud, \BristolSU\ControlDB\Contracts\Repositories\User $userRepository, \BristolSU\ControlDB\Contracts\Repositories\Group $groupRepository)
    {
        $this->unionCloud = $unionCloud;
        $this->userRepository = $userRepository;
        $this->groupRepository = $groupRepository;
    }

    /**
     * @inheritDoc
     */
    public function getUsersThroughGroup(Group $group): Collection
    {
        return GroupGroupMembership::userGroupsForGroup($group->id())->map(function(int $userGroupId) {
            return $this->unionCloud->getUserGroupMemberships($userGroupId);
        })->flatten(1)->map(function(UserGroupMembership $ugm) {
            try {
                return $this->userRepository->getByDataProviderId(DataUserModel::fromUnionCloudUser($ugm->user)->id());
            } catch (ModelNotFoundException $e) {
                return $this->userRepository->create(DataUserModel::fromUnionCloudUser($ugm->user)->id());
            }
        });
    }

    /**
     * @inheritDoc
     */
    public function getGroupsThroughUser(User $user): Collection
    {
        $ugmIds = $this->unionCloud->getUsersUserGroupMemberships($user->dataProviderId())
            ->map(function(UserGroupMembership $userGroupMembership) {
                return $userGroupMembership->usergroup->ug_id;
            });

        return $ugmIds->map(function(int $ugmId) {
            return GroupGroupMembership::groupsFromUserGroup($ugmId)->map(function(int $groupId) {
                return $this->groupRepository->getById($groupId);
            })->flatten(1);
        })->flatten(1);
    }

    /**
     * @inheritDoc
     */
    public function addUserToGroup(User $user, Group $group): void
    {
        throw new PermissionDeniedException('Unioncloud does not allow adding memberships');
    }

    /**
     * @inheritDoc
     */
    public function removeUserFromGroup(User $user, Group $group): void
    {
        throw new PermissionDeniedException('Unioncloud does not allow removing memberships');
    }
}
