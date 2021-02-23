<?php

namespace BristolSU\UnionCloud\Events;

use BristolSU\ControlDB\Contracts\Models\DataUser;
use BristolSU\ControlDB\Contracts\Models\Group;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsersWithMembershipToGroupRetrieved
{
    use Dispatchable, SerializesModels;

    /**
     * @var Group
     */
    public Group $group;

    /**
     * @var array|DataUser[]
     */
    public array $unionCloudUsers;

    /**
     * Create a new job instance.
     *
     * @param array|DataUser[] $unionCloudUsers
     */
    public function __construct(Group $group, array $unionCloudUsers)
    {
        $this->group = $group;
        $this->unionCloudUsers = $unionCloudUsers;
    }

}
