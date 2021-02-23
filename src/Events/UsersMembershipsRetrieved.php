<?php

namespace BristolSU\UnionCloud\Events;

use BristolSU\ControlDB\Contracts\Models\DataUser;
use BristolSU\ControlDB\Contracts\Models\Group;
use BristolSU\ControlDB\Contracts\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UsersMembershipsRetrieved
{
    use Dispatchable, SerializesModels;

    /**
     * @var User
     */
    public User $user;

    public array $groups;

    /**
     * Create a new job instance.
     *
     * @param array|Group[] $unionCloudUsers
     */
    public function __construct(User $user, array $groups)
    {
        $this->user = $user;
        $this->groups = $groups;
    }

}
