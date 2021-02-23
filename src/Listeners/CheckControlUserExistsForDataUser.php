<?php

namespace BristolSU\UnionCloud\Listeners;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Events\UserRetrieved;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CheckControlUserExistsForDataUser
{

    /**
     * @var UserRepository
     */
    public UserRepository $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Execute the job.
     *
     * @param UserRepository $repository
     * @return void
     */
    public function handle(UserRetrieved $userRetrieved)
    {
        try {
            $this->repository->getByDataProviderId($userRetrieved->unionCloudUser->uid);
        } catch (ModelNotFoundException $e) {
            $this->repository->create($userRetrieved->unionCloudUser->uid);
        }
    }

}
