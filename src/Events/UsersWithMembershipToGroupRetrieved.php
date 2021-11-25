<?php

namespace BristolSU\UnionCloud\Events;

use BristolSU\ControlDB\Contracts\Models\Group;
use BristolSU\ControlDB\Contracts\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

class UsersWithMembershipToGroupRetrieved
{
    use Dispatchable, SerializesModels;

    /**
     * @var Group
     */
    public Group $group;

    /**
     * @var Collection|User[]
     */
    public Collection $unionCloudUsers;

    /**
     * Create a new job instance.
     *
     * @param Group $group
     * @param Collection|User[] $unionCloudUsers
     */
    public function __construct(Group $group, Collection $unionCloudUsers)
    {
        $this->group = $group;
        $this->unionCloudUsers = $unionCloudUsers;
    }

}
