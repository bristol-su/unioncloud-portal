<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Jobs\CacheUser;
use BristolSU\UnionCloud\Jobs\CacheUsersUserGroupMemberships;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheUnionCloudUsersUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:cacheuserusergroups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache users user group memberships from UnionCloud';

    protected static $usersUserGroupMemberships = 30;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        for($i = 0; $i <= config('unioncloud-portal.user_user_groups_per_minute', static::$usersUserGroupMemberships); $i++) {
            dispatch(new CacheUsersUserGroupMemberships($this->getId()));
        }
    }

    private function getId(): int 
    {
        if(Cache::get('uc-ug-user-ids-to-cache', collect())->count() === 0) {
            Cache::forever('uc-ug-user-ids-to-cache', app(UserRepository::class)->all()->map(function(User $user) {
                return $user->dataProviderId();
            }));
        }
        
        $ids = Cache::get('uc-ug-user-ids-to-cache', collect());
        $id = $ids->shift();
        Cache::forever('uc-ug-user-ids-to-cache', $ids);
        return $id;
    }

}
