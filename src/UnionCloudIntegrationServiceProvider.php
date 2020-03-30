<?php

namespace BristolSU\UnionCloud;

use BristolSU\ControlDB\Contracts\Repositories\DataUser as DataUserRepositoryContract;
use BristolSU\ControlDB\Contracts\Repositories\Pivots\UserGroup as UserGroupRepositoryContract;
use BristolSU\UnionCloud\Commands\CacheUnionCloudDataUsers;
use BristolSU\UnionCloud\Commands\FindCachedUsers;
use BristolSU\UnionCloud\Implementations\DataUserRepository as UnionCloudDataUserRepository;
use BristolSU\UnionCloud\Implementations\UserGroup as UnionCloudUserGroupRepository;
use BristolSU\UnionCloud\UnionCloud\UnionCloud;
use BristolSU\UnionCloud\UnionCloud\UnionCloudCacher;
use BristolSU\UnionCloud\UnionCloud\UnionCloudContract;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Support\ServiceProvider;

class UnionCloudIntegrationServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->registerConfig();
        $this->registerMigrations();
        $this->registerCommands();
        
        $this->app->bind(UnionCloudContract::class, UnionCloud::class);
        $this->app->extend(UnionCloudContract::class, function(UnionCloudContract $service, $app) {
            return new UnionCloudCacher($service, $app->make(Repository::class));
        });
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
            CacheUnionCloudDataUsers::class,
            FindCachedUsers::class
        ]);
    }

}