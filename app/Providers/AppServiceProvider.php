<?php

namespace App\Providers;

use App\Models\CustomPermission;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\EventRecurrenceRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentActivityRepository;
use App\Repositories\Eloquent\EloquentBorrowingRepository;
use App\Repositories\Eloquent\EloquentEventRecurrenceRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            BorrowingRepositoryInterface::class,
            EloquentBorrowingRepository::class
        );

        $this->app->bind(
            EventRepositoryInterface::class,
            EloquentEventRepository::class
        );

        $this->app->bind(
            ActivityRepositoryInterface::class,
            EloquentActivityRepository::class
        );

        $this->app->bind(
            EventRecurrenceRepositoryInterface::class,
            EloquentEventRecurrenceRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::define('access', function (User $user, string $routeName) {
            // Cek apakah user memiliki permission langsung
            if ($user->hasPermissionTo(CustomPermission::where('route_name', $routeName)->pluck('name')->first())) {
                return true;
            }

            // Cek melalui role
            return $user->roles()->whereHas('permissions', function ($query) use ($routeName) {
                $query->where('route_name', $routeName);
            })->exists();
        });

        Collection::macro('toRecursive', function () {
            return $this->map(function ($item) {
                if (is_array($item)) {
                    return collect($item)->toRecursive();
                }
                return $item;
            });
        });
    }
}
