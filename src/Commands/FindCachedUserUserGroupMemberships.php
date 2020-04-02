<?php

namespace BristolSU\UnionCloud\Commands;

use BristolSU\ControlDB\Contracts\Models\User;
use BristolSU\ControlDB\Contracts\Repositories\User as UserRepository;
use BristolSU\UnionCloud\Implementations\DataUserRepository;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class FindCachedUserUserGroupMemberships extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:cacheduserusergroups';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'See all cached users user group memberships from UnionCloud';

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
        $done = $this->cachedIds()->count();
        $completed = $this->ids()->count();

        $this->line(
            sprintf('We\'ve cached %d%% of the users usergroup memberships (%d/%d).', ($done / $completed) * 100, $done, $completed)
        );
    }

    public function cachedIds()
    {
        return $this->ids()->filter(function (int $id) {
            return Cache::has('unioncloud-user-group-ugm-through-user:' . $id);
        });
    }

    public function ids()
    {
        return app(UserRepository::class)->all()->map(function (User $user) {
            return $user->dataProviderId();
        });
    }
}
