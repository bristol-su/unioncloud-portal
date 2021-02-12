<?php

namespace BristolSU\UnionCloud\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;

class getUsersData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $page;
    protected int $pageCount;
    protected int $delayBy;
    protected $repository;

    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param int $pageCount
     * @param int $delayBy
     */
    public function __construct(int $page, int $pageCount, int $delayBy)
    {
        $this->page = $page;
        $this->pageCount = $pageCount;
        $this->delayBy = $delayBy;

        // Init Repository
        $this->repository = app(UnionCloud::class);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $attributes = [
            'page' => $this->page
        ];

        $Users = $this->repository->getAllUsers($attributes, $this->page)->getRawData();
        foreach($Users as $User)
        {
            processUserData::dispatch($User);
        }
    }

    protected function triggerNext()
    {
        if(! $this->page === $this->pageCount) {
            getUsersData::dispatch($this->page + 1, $this->pageCount, $this->delayBy);
        } else {
            // Trigger Notification:

        }
    }
}
