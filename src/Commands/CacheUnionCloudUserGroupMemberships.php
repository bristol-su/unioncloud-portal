<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Jobs\CacheUser;
use BristolSU\UnionCloud\Jobs\CacheUserGroupMemberships;
use BristolSU\UnionCloud\Models\GroupGroupMembership;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CacheUnionCloudUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:cacheusergroups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cache user groups from UnionCloud';

    protected static $userGroupsToCache = 30;

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
        for($i = 0; $i <= config('unioncloud-portal.user_groups_per_minute', static::$userGroupsToCache); $i++) {
            dispatch(new CacheUserGroupMemberships($this->getId()));
        }
    }

    private function getId(): int 
    {
        if(Cache::get('uc-ug-ids-to-cache', collect())->count() === 0) {
            Cache::forever('uc-ug-ids-to-cache', GroupGroupMembership::all()->pluck('usergroup_id'));
        }
        
        $ids = Cache::get('uc-ug-ids-to-cache', collect());
        $id = $ids->shift();
        Cache::forever('uc-ug-ids-to-cache', $ids);
        return $id;
    }

}
