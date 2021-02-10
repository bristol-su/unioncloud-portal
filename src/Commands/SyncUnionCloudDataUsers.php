<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Cache\DataUser;
use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Implementations\DataUserRepository;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SyncUnionCloudDataUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync users from UnionCloud';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {

    }
 
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get Users from UC API as Array:

        // Check if they exist within Users Table ('Control_data_user') already or not

        // Add if don't exist
            // This could be processed via a job to enable async processing


    }
}
