<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Forms\MenuForm;
use App\Models\Menu as ModelsMenu;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Menu extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public MenuForm $form;
    #[Url(as: 'q')]
    public $search = '';
    public $routes = [];
    public $editId = null;
    public $deleteId = null;
    public $icons = [];

    public function updatedSearch()
    {
        $this->resetPage();
    }

    /**
     * Get HeroIcons with caching (24 hours)
     * Icons rarely change, so we cache them for a long time
     */
    public function getHeroIcons()
    {
        return Cache::remember('heroicons_list', 86400, function () {
            $solidIconsPath = base_path('vendor/blade-ui-kit/blade-heroicons/resources/svg');

            $solidIcons = File::files($solidIconsPath);

            $icons = [];

            // Process solid icons (s- prefix)
            foreach ($solidIcons as $file) {
                $name = str_replace('.svg', '', $file->getFilename());

                // Tambahkan filter untuk icon yang diawali dengan 'c-'
                if (Str::startsWith($name, 'c-')) {
                    $displayName = ucwords(str_replace('-', ' ', $file->getFilenameWithoutExtension()));
                    $icons[$name] = Str::replaceStart('C', '', $displayName);
                }
            }

            ksort($icons);

            return $icons;
        });
    }

    /**
     * Get available routes with caching (1 hour)
     */
    protected function getAvailableRoutes(): array
    {
        return Cache::remember('available_routes', 3600, function () {
            $routes = Route::getRoutes();
            $routeNames = [];

            $excludedPrefixes = ['ignition.', 'sanctum.', 'generated::', 'livewire.', 'telescope.', 'horizon.', 'debugbar.', 'nova.'];

            foreach ($routes as $route) {
                $name = $route->getName();

                if ($name && !Str::startsWith($name, $excludedPrefixes)) {
                    $routeNames[] = $name;
                }
            }

            return $routeNames;
        });
    }



    public function mount()
    {
        $this->authorize('access', 'admin.menus.index');

        $this->icons = $this->getHeroIcons();
        $this->routes = $this->getAvailableRoutes();
    }

    public function create()
    {
        $this->authorize('access', 'admin.menus.create');

        $this->form->reset();
        $this->dispatch('open-modal', 'menu-modal');
    }
    public function save()
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.menus.edit');

                $this->form->update();
                $this->editId = null;
                flash()->success('Menu updated successfully');
                Log::info('Menu updated via Livewire', ['user_id' => auth()->id()]);
            } else {
                $this->authorize('access', 'admin.menus.create');

                $this->form->store();
                flash()->success('Menu created successfully');
                Log::info('Menu created via Livewire', ['user_id' => auth()->id()]);
            }

            // Note: Cache invalidation is now handled by MenuObserver
            $this->dispatch('close-modal', 'menu-modal');
            $this->dispatch('menuUpdated');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized menu save attempt', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (\Exception $e) {
            Log::error('Failed to save menu', [
                'user_id' => auth()->id(),
                'edit_id' => $this->editId,
                'error' => $e->getMessage()
            ]);
            flash()->error('Failed to save menu. Please try again.');
        }
    }

    public function edit($id)
    {
        try {
            $this->authorize('access', 'admin.menus.edit');

            $menu = ModelsMenu::findOrFail($id);
            $this->editId = $id;
            $this->form->setDataMenu($menu);
            $this->dispatch('open-modal', 'menu-modal');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Menu not found for edit', ['menu_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Menu not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized menu edit attempt', ['menu_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to edit this menu.');
        } catch (\Exception $e) {
            Log::error('Failed to load menu for edit', [
                'menu_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load menu. Please try again.');
        }
    }

    public function confirmDelete($id)
    {
        try {
            $this->authorize('access', 'admin.menus.destroy');

            $menu = ModelsMenu::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setDataMenu($menu);
            $this->dispatch('open-modal', 'delete-menu-confirmation');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::warning('Menu not found for delete confirmation', ['menu_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('Menu not found.');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized menu delete attempt', ['menu_id' => $id, 'user_id' => auth()->id()]);
            flash()->error('You are not authorized to delete this menu.');
        } catch (\Exception $e) {
            Log::error('Failed to prepare menu deletion', [
                'menu_id' => $id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to prepare deletion. Please try again.');
        }
    }

    public function delete()
    {
        try {
            $this->authorize('access', 'admin.menus.destroy');

            $menuName = $this->form->menu; // Store for logging
            $this->form->delete();
            $this->deleteId = null;

            flash()->success('Menu deleted successfully');
            Log::info('Menu deleted via Livewire', [
                'menu_name' => $menuName,
                'user_id' => auth()->id(),
            ]);

            // Note: Cache invalidation is now handled by MenuObserver
            $this->dispatch('close-modal', 'delete-menu-confirmation');
            $this->dispatch('menuUpdated');
        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            Log::warning('Unauthorized menu delete attempt', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
            ]);
            flash()->error('You are not authorized to delete this menu.');
        } catch (\Exception $e) {
            Log::error('Failed to delete menu', [
                'user_id' => auth()->id(),
                'delete_id' => $this->deleteId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            flash()->error('Failed to delete menu. Please try again.');
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    public function render()
    {
        $this->authorize('access', 'admin.menus.index');

        $table_heads = ['No', 'Main Menu', 'Menu', 'Route Name', 'Icon', 'Sort', 'Action'];

        $menus = ModelsMenu::when($this->search, function ($query) {
            $query->where(function ($q) {
                $q->where('menu', 'like', '%' . $this->search . '%')
                    ->orWhere('main_menu', 'like', '%' . $this->search . '%')
                    ->orWhere('route_name', 'like', '%' . $this->search . '%');
            });
        })
            ->orderBy('sort')
            ->paginate(10);

        return view('livewire.admin.pages.menu', compact('table_heads', 'menus'));
    }
}
