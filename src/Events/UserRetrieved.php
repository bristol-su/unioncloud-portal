<?php

namespace BristolSU\UnionCloud\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Twigger\UnionCloud\API\Resource\User;

class UserRetrieved
{
    use Dispatchable, SerializesModels;

    /**
     * The unioncloud user
     *
     * @var User
     */
    public User $unionCloudUser;

    /**
     * Create a new job instance.
     *
     * @param User $unionCloudUser
     */
    public function __construct(User $unionCloudUser)
    {
        $this->unionCloudUser = $unionCloudUser;
    }

}
