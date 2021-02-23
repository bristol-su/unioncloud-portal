<?php

namespace BristolSU\UnionCloud\Jobs;

use BristolSU\UnionCloud\Commands\SyncUnionCloudDataUsers;
use BristolSU\UnionCloud\Events\UserRetrieved;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RetrieveUserMemberships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $page;

    protected int $totalPages;

    protected int $delayBy;

    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param int $totalPages
     * @param int $delayBy
     */
    public function __construct(int $page, int $totalPages, int $delayBy)
    {
        $this->page = $page;
        $this->totalPages = $totalPages;
        $this->delayBy = $delayBy;
    }

    /**
     * Execute the job.
     *
     * @param UnionCloudContract $unionCloud
     * @return void
     * @throws \Exception
     */
    public function handle(UnionCloudContract $unionCloud)
    {
        $unionCloudResponse = $unionCloud->getAllUsers(
            $this->page,
            SyncUnionCloudDataUsers::RECORDS_PER_PAGE
        );

        $users = $unionCloudResponse->get();

        foreach ($users as $user) {
            UserRetrieved::dispatch($user);
        }

        $this->triggerNext();
    }

    protected function triggerNext(): void
    {
        if ($this->totalPages > $this->page) {
            RetrieveUsers::dispatch(
                $this->page + 1,
                $this->totalPages,
                $this->delayBy,
            )->delay(
                now()->addSeconds(
                    $this->delayBy
                )
            );
        }
    }
}
