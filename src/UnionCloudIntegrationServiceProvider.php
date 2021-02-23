<?php

namespace BristolSU\UnionCloud;

use BristolSU\ControlDB\Contracts\Repositories\DataUser as DataUserRepositoryContract;
use BristolSU\ControlDB\Contracts\Repositories\Pivots\UserGroup as UserGroupRepositoryContract;
use BristolSU\UnionCloud\Cache\IdCacheStore;
use BristolSU\UnionCloud\Cache\IdStore;
use BristolSU\UnionCloud\Commands\CacheUnionCloudDataUsers;
use BristolSU\UnionCloud\Commands\CacheUnionCloudUserGroupMemberships;
use BristolSU\UnionCloud\Commands\CacheUnionCloudUsersUserGroupMemberships;
use BristolSU\UnionCloud\Commands\SyncUnionCloudDataUsers;
use BristolSU\UnionCloud\Events\UserRetrieved;
use BristolSU\UnionCloud\Events\UsersMembershipsRetrieved;
use BristolSU\UnionCloud\Events\UsersWithMembershipToGroupRetrieved;
use BristolSU\UnionCloud\Implementations\DataUserRepository as UnionCloudDataUserRepository;
use BristolSU\UnionCloud\Implementations\UserGroup as UnionCloudUserGroupRepository;
use BristolSU\UnionCloud\Listeners\CacheDataUser;
use BristolSU\UnionCloud\Listeners\CacheUsersMemberships;
use BristolSU\UnionCloud\Listeners\CacheUsersWithMembershipToGroup;
use BristolSU\UnionCloud\Listeners\CheckControlUserExistsForDataUser;
use BristolSU\UnionCloud\Listeners\LogDataUserRetrieval;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use BristolSU\UnionCloud\UnionCloud\UnionCloudCacher;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class UnionCloudIntegrationServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();

        $this->app->bind(UnionCloudContract::class, UnionCloud::class);

        $this->app->when(CacheUnionCloudUserGroupMemberships::class)
            ->needs(IdStore::class)
            ->give(function() {
                return new IdCacheStore('uc-ug-ids-to-cache', app(Repository::class));
            });

        $this->app->when(CacheUnionCloudUsersUserGroupMemberships::class)
            ->needs(IdStore::class)
            ->give(function() {
                return new IdCacheStore('uc-ug-user-ids-to-cache', app(Repository::class));
            });

        Event::listen(UserRetrieved::class, CheckControlUserExistsForDataUser::class);
        Event::listen(UserRetrieved::class, CacheDataUser::class);
        if(config('app.debug', false)) {
            Event::listen(UserRetrieved::class, LogDataUserRetrieval::class);
        }
        Event::listen(UsersWithMembershipToGroupRetrieved::class, CacheUsersWithMembershipToGroup::class);
        Event::listen(UsersMembershipsRetrieved::class, CacheUsersMemberships::class);

    }

    protected function registerConfig()
    {
        $this->publishes([__DIR__ .'/../config/unioncloud-portal.php' => config_path('unioncloud-portal.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__ .'/../config/unioncloud-portal.php', 'unioncloud-portal'
        );
    }

    protected function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    public function boot()
    {
        if(config('unioncloud-portal.enabled.data-users', false)) {
            $this->app->bind(DataUserRepositoryContract::class, UnionCloudDataUserRepository::class);
        }
        if(config('unioncloud-portal.enabled.memberships', false)) {
            $this->app->bind(UserGroupRepositoryContract::class, UnionCloudUserGroupRepository::class);
        }
    }

    public function registerCommands()
    {
        $this->commands([
            CacheUnionCloudUserGroupMemberships::class,
            CacheUnionCloudUsersUserGroupMemberships::class,
            SyncUnionCloudDataUsers::class
        ]);
    }

}
