<?php

namespace BristolSU\UnionCloud\Jobs;

use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Twigger\UnionCloud\API\Resource\User;

class ProcessUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $repository;

    private User $unioncloudUser;

    /**
     * Create a new job instance.
     *
     * @param User $unioncloudUser
     */
    public function __construct(User $unioncloudUser)
    {
        $this->unioncloudUser = $unioncloudUser;
    }

    /**
     * Execute the job.
     *
     * @param UserRepository $repository
     * @return void
     */
    public function handle(UserRepository $repository)
    {
        try {
            $user = $repository->getByDataProviderId($this->user['uid']);
            $repository->update($user->id(), $this->user['uid']);
        } catch (ModelNotFoundException $e) {
            $repository->create($this->user['uid']);
        }
    }
}
