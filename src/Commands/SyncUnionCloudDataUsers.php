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

    // Number of times to be run per minute
    protected int $requestRate = 10;

    protected int $page = 1;
    protected int $pageCount;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        // Update Request Rate if set in .env
        $this->requestRate = config('unioncloud-portal.user_requests_rate');
    }

    /**
     * Execute the console command.
     * @param UnionCloud $unionCloud
     * @throws \Exception
     */
    public function handle(UnionCloud $unionCloud)
    {
        $attributes = [
            'records_per_page' => 500,
            'page' => $this->page
        ];

        try {
            $users = $unionCloud->getAllUsers($attributes, $this->page)->getRawData();
        } catch (\Exception $e) {
            if ($e instanceof ClientException && $e->getCode() === 403) {
                throw new \Exception('Could not connect to UnionCloud', $e->getCode(), $e);
            } else {
                throw new \Exception('An error occured while retrieving user data from UnionCloud', $e->getCode(), $e);
            }
        }

        $this->pageCount = $response->getTotalPages();

        // Adjust factor to ensure always less than request rate per minute:
        $factor = ceil(60/($this->requestRate * 0.8));

        // Get Users from UC API as Array:
        $Users = $response->getRawData();

        GetUsersData::dispatch($this->page, $this->pageCount, $factor, $Users);
    }
}
