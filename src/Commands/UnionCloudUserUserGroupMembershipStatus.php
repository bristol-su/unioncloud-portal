<?php


namespace BristolSU\UnionCloud\Commands;


use BristolSU\ControlDB\Contracts\Models\User as UserModel;
use BristolSU\ControlDB\Contracts\Repositories\User;
use BristolSU\UnionCloud\Cache\IdStore;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class UnionCloudUserUserGroupMembershipStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:usermemberships:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check through the status of the unioncloud users membership caching.';

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
    public function handle(User $userRepository, CacheRepository $cache)
    {
        $users = $userRepository->all();
        $totalUserCount = $users->count();
        $cachedUserCount = $users->filter(
            fn(UserModel $user) => $cache->has(\BristolSU\ControlDB\Cache\Pivots\UserGroup::class . '@getGroupsThroughUser:' . $user->id())
        )->count();

        /*
         * Get the remaining users to be processed, which is the total users if none in the ID store as the
         * next time the cache command runs it'll populate the ID store again.
         */
        $remainingUserCount = $this->idStore->count();
        if($remainingUserCount === 0) {
            $remainingUserCount = $totalUserCount;
        }

        $minutesToFinish = (int) ceil($remainingUserCount / config('unioncloud-portal.user_user_groups_per_minute'));
        $timeToFinish = Carbon::now()->addMinutes($minutesToFinish);
        $this->info(sprintf('Cached the memberships for %u/%u users.', $cachedUserCount, $totalUserCount));
        $this->info(sprintf('All users should have been cached in approximately %s.', $timeToFinish->longAbsoluteDiffForHumans(Carbon::now())));
    }
}
