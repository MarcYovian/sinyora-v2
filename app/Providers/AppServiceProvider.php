<?php

namespace App\Providers;

use App\Events\ContactSubmitted;
use App\Listeners\SendContactNotifications;
use App\Models\CustomPermission;
use App\Models\User;
use App\Repositories\Contracts\ActivityRepositoryInterface;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use App\Repositories\Contracts\AssetRepositoryInterface;
use App\Repositories\Contracts\BorrowingDocumentRepositoryInterface;
use App\Repositories\Contracts\BorrowingRepositoryInterface;
use App\Repositories\Contracts\ContentSettingRepositoryInterface;
use App\Repositories\Contracts\DocumentRepositoryInterface;
use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use App\Repositories\Contracts\EventRecurrenceRepositoryInterface;
use App\Repositories\Contracts\EventRepositoryInterface;
use App\Repositories\Contracts\InvitationDocumentRepositoryInterface;
use App\Repositories\Contracts\LicensingDocumentRepositoryInterface;
use App\Repositories\Contracts\LocationRepositoryInterface;
use App\Repositories\Contracts\MassScheduleRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentActivityRepository;
use App\Repositories\Eloquent\EloquentArticleRepository;
use App\Repositories\Eloquent\EloquentAssetRepository;
use App\Repositories\Eloquent\EloquentBorrowingDocumentRepository;
use App\Repositories\Eloquent\EloquentBorrowingRepository;
use App\Repositories\Eloquent\EloquentContentSettingRepository;
use App\Repositories\Eloquent\EloquentDocumentRepository;
use App\Repositories\Eloquent\EloquentEventCategoryRepository;
use App\Repositories\Eloquent\EloquentEventRecurrenceRepository;
use App\Repositories\Eloquent\EloquentEventRepository;
use App\Repositories\Eloquent\EloquentInvitationDocumentRepository;
use App\Repositories\Eloquent\EloquentLicensingDocumentRepository;
use App\Repositories\Eloquent\EloquentLocationRepository;
use App\Repositories\Eloquent\EloquentMassScheduleRepository;
use App\Repositories\Eloquent\EloquentOrganizationRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\SEOService;
use Illuminate\Support\Facades\Event;
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
        $this->app->singleton(SEOService::class, function ($app) {
            return new SEOService();
        });
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

        $this->app->bind(
            DocumentRepositoryInterface::class,
            EloquentDocumentRepository::class
        );

        $this->app->bind(
            LocationRepositoryInterface::class,
            EloquentLocationRepository::class
        );

        $this->app->bind(
            OrganizationRepositoryInterface::class,
            EloquentOrganizationRepository::class
        );

        $this->app->bind(
            AssetRepositoryInterface::class,
            EloquentAssetRepository::class
        );

        $this->app->bind(
            EventCategoryRepositoryInterface::class,
            EloquentEventCategoryRepository::class
        );

        $this->app->bind(
            InvitationDocumentRepositoryInterface::class,
            EloquentInvitationDocumentRepository::class
        );

        $this->app->bind(
            LicensingDocumentRepositoryInterface::class,
            EloquentLicensingDocumentRepository::class
        );

        $this->app->bind(
            BorrowingDocumentRepositoryInterface::class,
            EloquentBorrowingDocumentRepository::class
        );

        $this->app->bind(
            ArticleRepositoryInterface::class,
            EloquentArticleRepository::class
        );

        $this->app->bind(
            ContentSettingRepositoryInterface::class,
            EloquentContentSettingRepository::class
        );

        $this->app->bind(
            MassScheduleRepositoryInterface::class,
            EloquentMassScheduleRepository::class
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

        // Register Role Observer for Spatie Role model
        \Spatie\Permission\Models\Role::observe(\App\Observers\RoleObserver::class);

        // Register Event and EventRecurrence Observers for cache invalidation
        \App\Models\Event::observe(\App\Observers\EventObserver::class);
        \App\Models\EventRecurrence::observe(\App\Observers\EventRecurrenceObserver::class);

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
    }
}

