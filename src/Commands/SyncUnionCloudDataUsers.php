<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\UnionCloud\Jobs\getUsersData;
use BristolSU\UnionCloud\Jobs\notifyAdmin;
use BristolSU\UnionCloud\Jobs\processUserData;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use Illuminate\Support\Facades\Log;


class SyncUnionCloudDataUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:sync:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from UnionCloud';

    // Not currently working as Page Limit doesn't work on the Package.
    protected int $requestLimit = 1;

    // Number of times to be run per minute
    protected int $requestRate = 10;

    protected int $page = 1;
    protected int $pageCount;

    /**
     * @var UnionCloud
     */
    private $repository;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // Update Request Limit if set in .env
        $this->requestLimit = config('unioncloud-portal.users_per_minute');

        // Update Request Rate if set in .env
        $this->requestRate = config('unioncloud-portal.user_requests_rate');

        // Init Repository
        $this->repository = app(UnionCloud::class);
    }
 
    /**
     * Execute the console command.
     */
    public function handle()
    {
        $attributes = [
            'records_per_page' => $this->requestLimit,
            'page' => $this->page
        ];

        try {
            $response = $this->repository->getAllUsers($attributes, $this->page);
        } catch (\Exception $e) {
            if($e instanceof ClientException && $e->getCode() === 403) {
                $this->error('Failed to reach UC');
                return;
            } else {
                throw $e;
            }
        }

        $this->pageCount = $response->getTotalPages();

        // Adjust factor to ensure always less than request rate per minute:
        $factor = ceil(60/($this->requestRate * 0.8));

        // Get Users from UC API as Array:
        $Users = $response->getRawData();
        foreach($Users as $User)
        {
            processUserData::dispatch($User);
        }

        // Offset Page by 1 as this request will return the 1st Page:
        if($this->pageCount > $this->page) {
            getUsersData::dispatch($this->page + 1, $this->pageCount, $factor)->delay(now()->addSeconds($factor));
        }
    }
}
