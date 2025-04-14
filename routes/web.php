<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::group(['prefix' => 'admin', 'middleware' => ['auth'], 'as' => 'admin.'], function () {
    Route::view('dashboard', 'dashboard')
        ->name('dashboard.index');

    Route::view('profile', 'profile')
        ->name('profile');

    Route::get('menu', \App\Livewire\Admin\Pages\Menu::class)
        ->name('menu.index');
    Route::get('public-menu', \App\Livewire\Admin\Pages\PublicMenu::class)
        ->name('public-menu.index');

    Route::get('user', \App\Livewire\Admin\Pages\User::class)
        ->name('users.index');

    Route::get('role', \App\Livewire\Admin\Pages\Role::class)
        ->name('roles.index');
    Route::get('permission', \App\Livewire\Admin\Pages\Permission::class)
        ->name('permissions.index');

    Route::get('groups', \App\Livewire\Admin\Pages\Groups::class)
        ->name('groups.index');

    Route::get('event-category', \App\Livewire\Admin\Pages\EventCategory::class)
        ->name('event-category.index');

    Route::get('organization', \App\Livewire\Admin\Pages\Organization::class)
        ->name('organizations.index');

    Route::get('location', \App\Livewire\Admin\Pages\Location::class)
        ->name('locations.index');

    Route::get('event', \App\Livewire\Admin\Pages\Event\Index::class)
        ->name('events.index');

    Route::get('event/{event}/show', \App\Livewire\Admin\Pages\Event\Show::class)
        ->name('event.show');

    Route::get('event/{event}/recurrence/edit', \App\Livewire\Admin\Pages\Event\Recurrence::class)
        ->name('events.recurrences.edit');
});



require __DIR__ . '/auth.php';
