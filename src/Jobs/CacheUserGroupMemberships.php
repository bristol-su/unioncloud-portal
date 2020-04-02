<?php

namespace BristolSU\UnionCloud\Jobs;

use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use BristolSU\UnionCloud\UnionCloud\UnionCloudCacher;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CacheUserGroupMemberships implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * @var int
     */
    private $userGroupId;

    public function __construct(int $userGroupId)
    {
        $this->userGroupId = $userGroupId;
        $this->queue = 'unioncloud-user-cache';
    }

    public function handle()
    {
        $repository = new UnionCloudCacher(
            new UnionCloud(app(\Twigger\UnionCloud\API\UnionCloud::class)),
            app(Repository::class)
        );
        $repository->getUserGroupMemberships($this->userGroupId);
    }
}