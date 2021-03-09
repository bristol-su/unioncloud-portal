<?php


namespace BristolSU\UnionCloud\Commands;


use BristolSU\ControlDB\Contracts\Models\User as UserModel;
use BristolSU\ControlDB\Contracts\Repositories\User;
use Illuminate\Console\Command;
use Illuminate\Contracts\Cache\Repository as CacheRepository;

class UnionCloudDataUserStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'unioncloud:users:sync:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check through the status of the unioncloud user caching.';

    /**
     * Execute the console command.
     */
    public function handle(User $userRepository, CacheRepository $cache)
    {
        $users = $userRepository->all();
        $totalUserCount = $users->count();
        $cachedUserCount = $users->filter(
            fn(UserModel $user) => $cache->has(\BristolSU\ControlDB\Cache\DataUser::class . '@getById:' . $user->dataProviderId())
        )->count();

        $this->info(sprintf('Cached %u/%u users.', $cachedUserCount, $totalUserCount));
    }
}
