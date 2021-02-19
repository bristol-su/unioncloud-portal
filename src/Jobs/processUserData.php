<?php

namespace BristolSU\UnionCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use BristolSU\Support\User\User;
use Illuminate\Support\Facades\Log;

class ProcessUserData implements ShouldQueue
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

        try {
            $User = $this->repository->getByDataProviderId($this->user['uid']);
            $this->repository->update($User->id(), $this->user['uid']);
        } catch (ModelNotFoundException $e) {
            $this->repository->create($this->user['uid']);
        }
    }
}
