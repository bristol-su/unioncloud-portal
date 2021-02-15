<?php

namespace BristolSU\UnionCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use BristolSU\Support\User\User;

class processUserData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected array $user;
    protected $repository;

    /**
     * Create a new job instance.
     *
     * @param array $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $this->repository = app(\BristolSU\ControlDB\Contracts\Repositories\User::class);

        $User = $this->repository->getById($this->user['uid']);

        if($User) {
            // If Exists Update:
            $this->repository->update($this->user['uid'], $User->id());
        } else {
            // If doesn't exist then insert:
            $this->repository->create($this->user['uid']);
        }
    }
}
