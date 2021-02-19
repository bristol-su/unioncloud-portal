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
use Illuminate\Support\Facades\Log;

class GetUsersData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected int $page;
    protected int $pageCount;
    protected int $delayBy;
    protected array $users;
    protected $repository;

    protected int $requestLimit = 1;

    /**
     * Create a new job instance.
     *
     * @param int $page
     * @param int $pageCount
     * @param int $delayBy
     * @param array $users
     */
    public function __construct(int $page, int $pageCount, int $delayBy, array $users = [])
    {
        $this->page = $page;
        $this->pageCount = $pageCount;
        $this->delayBy = $delayBy;
        $this->users = $users;

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

        // If No Users are passed through then process get request:
        if(! $this->users) {
            try {
                $this->users = $this->repository->getAllUsers($attributes, $this->page)->getRawData();
            } catch (\Exception $e) {
                if ($e instanceof ClientException && $e->getCode() === 403) {
                    $this->error('Failed to reach UC');
                    return;
                } else {
                    Log::error($e, $e->getCode(), 'Exception thrown from getUsersData process');
                    throw $e;
                }
            }
        }

        foreach($this->users as $User)
        {
            ProcessUserData::dispatch($User);
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
