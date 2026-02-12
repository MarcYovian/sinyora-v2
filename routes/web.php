<?php

use App\Http\Controllers\TrixAttachmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', \App\Livewire\Pages\Home\Index::class)
    ->name('home.index');

Route::get('/events', \App\Livewire\Pages\Event\Index::class)
    ->name('events.index');

Route::get('/articles', \App\Livewire\Pages\Article\Index::class)
    ->name('articles.index');

Route::get('/articles/preview/{token}', \App\Livewire\Pages\Article\Preview::class)
    ->name('articles.preview')
    ->middleware(['auth']);

Route::get('/articles/{article:slug}', \App\Livewire\Pages\Article\Show::class)
    ->name('articles.show');

Route::get('/borrowing-assets', \App\Livewire\Pages\Borrowing\IndexComponent::class)
    ->name('borrowing.assets.index');

Route::redirect('/admin', '/admin/dashboard', 301);

Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'verified'], 'as' => 'admin.'], function () {
    Route::get('dashboard', \App\Livewire\Admin\Pages\Dashboard::class)
        ->name('dashboard.index')
        ->middleware(['permission:view dashboard']);

    Route::view('profile', 'profile')
        ->name('profile.index')
        ->middleware(['permission:view profile']);

    Route::get('menus', \App\Livewire\Admin\Pages\Menu::class)
        ->name('menus.index')
        ->middleware(['permission:view menus']);

    Route::get('public-menus', \App\Livewire\Admin\Pages\PublicMenu::class)
        ->name('public-menus.index');

    Route::get('users', \App\Livewire\Admin\Pages\User::class)
        ->name('users.index')
        ->middleware(['permission:view users']);

    Route::get('roles', \App\Livewire\Admin\Pages\Role::class)
        ->name('roles.index')
        ->middleware(['permission:view roles']);

    Route::get('permissions', \App\Livewire\Admin\Pages\Permission::class)
        ->name('permissions.index')
        ->middleware(['permission:view permissions']);

    Route::get('groups', \App\Livewire\Admin\Pages\Groups::class)
        ->name('groups.index')
        ->middleware(['permission:view groups']);

    Route::get('event-categories', \App\Livewire\Admin\Pages\EventCategory::class)
        ->name('event-categories.index')
        ->middleware(['permission:view event categories']);

    Route::get('organizations', \App\Livewire\Admin\Pages\Organization::class)
        ->name('organizations.index')
        ->middleware(['permission:view organizations']);

    Route::get('locations', \App\Livewire\Admin\Pages\Location::class)
        ->name('locations.index')
        ->middleware(['permission:view locations']);

    Route::get('contacts', \App\Livewire\Admin\Pages\Contact::class)
        ->name('contacts.index')
        ->middleware(['permission:view contacts']);


    Route::get('events', \App\Livewire\Admin\Pages\Event\Index::class)
        ->name('events.index')
        ->middleware(['permission:view events']);

    Route::get('events/{event}', \App\Livewire\Admin\Pages\Event\Show::class)
        ->name('events.show')
        ->middleware(['permission:view event details']);

    Route::get('events/{event}/recurrences', \App\Livewire\Admin\Pages\Event\Recurrence::class)
        ->name('events.recurrences.index')
        ->middleware(['permission:view event recurrences']);

    Route::get('asset-categories', \App\Livewire\Admin\Pages\Asset\Category::class)
        ->name('asset-categories.index')
        ->middleware(['permission:view asset categories']);

    Route::get('assets', \App\Livewire\Admin\Pages\Asset\Index::class)
        ->name('assets.index')
        ->middleware(['permission:view assets']);

    Route::get('asset-borrowings', \App\Livewire\Admin\Pages\Borrowing\Index::class)
        ->name('asset-borrowings.index')
        ->middleware(['permission:view asset borrowings']);

    Route::get('asset-borrowings/create', \App\Livewire\Admin\Pages\Borrowing\Create::class)
        ->name('asset-borrowings.create')
        ->middleware(['permission:create asset borrowing']);

    Route::get('asset-borrowings/{borrowing}/edit', \App\Livewire\Admin\Pages\Borrowing\Edit::class)
        ->name('asset-borrowings.edit')
        ->middleware(['permission:edit asset borrowing']);

    Route::get('articles/categories', \App\Livewire\Admin\Pages\Article\Category::class)
        ->name('articles.categories.index')
        ->middleware(['permission:view article categories']);

    Route::get('articles/tags', \App\Livewire\Admin\Pages\Article\Tag::class)
        ->name('articles.tags.index')
        ->middleware(['permission:view article tags']);

    Route::get('articles', \App\Livewire\Admin\Pages\Article\Index::class)
        ->name('articles.index')
        ->middleware(['permission:view articles']);

    // Pindahkan create sebelum edit
    Route::get('articles/create', \App\Livewire\Admin\Pages\Article\Form::class)
        ->name('articles.create')
        ->middleware(['permission:create article']);

    Route::get('articles/{id}/edit', \App\Livewire\Admin\Pages\Article\Form::class)
        ->name('articles.edit')
        ->middleware(['permission:edit article']);

    Route::get('documents', \App\Livewire\Admin\Pages\Document::class)
        ->name('documents.index')
        ->middleware(['permission:view documents']);

    Route::get('documents/invitation', \App\Livewire\Admin\Pages\Document\InvitationDocument::class)
        ->name('documents.invitation.index');
    // ->middleware(['permission:view invitation documents']);

    Route::group(['prefix' => 'content', 'as' => 'content.'], function () {
        Route::get('home', \App\Livewire\Admin\Pages\Content\Home::class)
            ->name('home')
            ->middleware(['permission:view content home']);

        Route::get('mass-schedules', \App\Livewire\Admin\Pages\Content\MassSchedule::class)
            ->name('mass-schedules');
        // ->middleware(['permission:view content mass schedules']);
    });
});

Route::post('/trix-attachments', [TrixAttachmentController::class, 'store'])
    ->name('trix-file-upload')
    ->middleware('auth');
Route::delete('/trix-attachments', [TrixAttachmentController::class, 'destroy'])
    ->name('trix-file-delete')
    ->middleware('auth');

require __DIR__ . '/auth.php';
