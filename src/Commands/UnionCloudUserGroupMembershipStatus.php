<?php


namespace BristolSU\UnionCloud\Commands;


use BristolSU\ControlDB\Contracts\Models\Group as GroupModel;
use BristolSU\ControlDB\Contracts\Repositories\Group;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use BristolSU\UnionCloud\Implementations\UserGroup;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;

class UnionCloudUserGroupMembershipStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:members:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check through the status of the unioncloud group membership caching.';

    /**
     * @var IdStore
     */
    private IdStore $idStore;

    /**
     * Create a new command instance.
     *
     * @param IdStore $idStore
     */
    public function __construct(IdStore $idStore)
    {
        parent::__construct();
        $this->idStore = $idStore;
    }

    /**
     * Execute the console command.
     */
    public function handle(Group $groupRepository, CacheRepository $cache)
    {
        $groups = $groupRepository->all();
        $totalGroupCount = $groups->count();
        $cachedGroupCount = $groups->filter(
            fn(GroupModel $group) => $cache->has(\BristolSU\ControlDB\Cache\Pivots\UserGroup::class . '@getUsersThroughGroup:' . $group->id())
        )->count();

        /*
         * Get the remaining groups to be processed, which is the total groups if none in the ID store as the
         * next time the cache command runs it'll populate the ID store again.
         */
        $remainingGroupCount = $this->idStore->count();
        if($remainingGroupCount === 0) {
            $remainingGroupCount = $totalGroupCount;
        }

        $minutesToFinish = (int) ceil($remainingGroupCount / config('unioncloud-portal.user_groups_per_minute'));
        $timeToFinish = Carbon::now()->addMinutes($minutesToFinish);
        $this->info(sprintf('Cached the memberships for %u/%u groups.', $cachedGroupCount, $totalGroupCount));
        $this->info(sprintf('All groups should have been cached in approximately %s.', $timeToFinish->longAbsoluteDiffForHumans(Carbon::now())));
    }
}
