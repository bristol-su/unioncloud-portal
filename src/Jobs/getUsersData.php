<?php

namespace BristolSU\UnionCloud\Jobs;

use BristolSU\Support\User\User;
use GuzzleHttp\Exception\ClientException;
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

    protected int $requestLimit = 1;

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

        $this->requestLimit = config('unioncloud-portal.users_per_minute');

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
            'records_per_page' => $this->requestLimit,
            'page' => $this->page
        ];

        try {
            $Users = $this->repository->getAllUsers($attributes, $this->page)->getRawData();
        } catch (\Exception $e) {
            if($e instanceof ClientException && $e->getCode() === 403) {
                $this->error('Failed to reach UC');
                return;
            } else {
                throw $e;
            }
        }

        foreach($Users as $User)
        {
            processUserData::dispatch($User);
        }

        $this->triggerNext();
    }

    protected function triggerNext()
    {
        if($this->pageCount > $this->page) {
            getUsersData::dispatch($this->page + 1, $this->pageCount, $this->delayBy)->delay(now()->addSeconds($this->delayBy));
        }
    }
}
