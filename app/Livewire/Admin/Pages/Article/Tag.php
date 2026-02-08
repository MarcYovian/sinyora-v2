<?php

namespace App\Livewire\Admin\Pages\Article;

use App\Livewire\Forms\TagForm;
use App\Models\Tag as ModelsTag;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Tag extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public TagForm $form;

    #[Url(as: 'q')]
    public string $search = '';

    public ?int $editId = null;
    public ?int $deleteId = null;
    public string $correlationId = '';

    public array $table_heads = ['No', 'Name', 'Slug', 'Articles', 'Actions'];

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->authorize('access', 'admin.articles.tags.index');
        $this->correlationId = Str::uuid()->toString();
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Reset all filters and pagination.
     */
    public function generateSlug(): void
    {
        $this->form->slug = str($this->form->name)->slug();
    }

    /**
     * Reset all filters and pagination.
     */
    public function resetFilters(): void
    {
        $this->reset('search');
        $this->resetPage();
    }

    /**
     * Open create modal.
     */
    public function create(): void
    {
        try {
            $this->authorize('access', 'admin.articles.tags.create');

            $this->form->reset();
            $this->editId = null;
            $this->deleteId = null;
            $this->dispatch('open-modal', 'tag-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized tag create attempt', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to create tags.');
        }
    }

    /**
     * Open edit modal for a tag.
     */
    public function edit(int $id): void
    {
        try {
            $this->authorize('access', 'admin.articles.tags.edit');

            $tag = ModelsTag::findOrFail($id);
            $this->editId = $id;
            $this->form->setTag($tag);
            $this->dispatch('open-modal', 'tag-modal');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized tag edit attempt', [
                'user_id' => Auth::id(),
                'tag_id' => $id,
            ]);
            flash()->error('You are not authorized to edit tags.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Tag not found for edit', ['tag_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Tag not found.');
        } catch (\Exception $e) {
            Log::error('Failed to load tag for edit', [
                'tag_id' => $id,
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
            ]);
            flash()->error('Failed to load tag. Please try again.');
        }
    }

    /**
     * Save tag (create or update).
     */
    public function save(): void
    {
        Log::info('Tag save action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'edit_id' => $this->editId,
        ]);

        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.articles.tags.edit');

                $this->form->update();
                flash()->success('Tag updated successfully.');

                Log::info('Tag updated successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'tag_id' => $this->editId,
                ]);
            } else {
                $this->authorize('access', 'admin.articles.tags.create');

                $tag = $this->form->store();
                flash()->success('Tag created successfully.');

                Log::info('Tag created successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'tag_id' => $tag->id,
                ]);
            }

            $this->dispatch('close-modal', 'tag-modal');
            $this->editId = null;
            $this->deleteId = null;
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized tag save attempt', [
                'user_id' => Auth::id(),
                'action' => $this->editId ? 'edit' : 'create',
            ]);
            flash()->error('You are not authorized to perform this action.');
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            Log::error('Tag save failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while saving the tag. Please try again later. #{$this->correlationId}");
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        try {
            $this->authorize('access', 'admin.articles.tags.destroy');

            $tag = ModelsTag::findOrFail($id);
            $this->deleteId = $id;
            $this->form->setTag($tag);
            $this->dispatch('open-modal', 'delete-tag-confirmation');
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized tag delete attempt', [
                'user_id' => Auth::id(),
                'tag_id' => $id,
            ]);
            flash()->error('You are not authorized to delete tags.');
        } catch (ModelNotFoundException $e) {
            Log::warning('Tag not found for delete', ['tag_id' => $id, 'user_id' => Auth::id()]);
            flash()->error('Tag not found.');
        }
    }

    /**
     * Delete a tag.
     */
    public function delete(): void
    {
        Log::info('Tag deletion action initiated', [
            'user_id' => Auth::id(),
            'correlation_id' => $this->correlationId,
            'tag_id' => $this->deleteId,
        ]);

        try {
            $this->authorize('access', 'admin.articles.tags.destroy');

            if ($this->deleteId) {
                $tagName = $this->form->name;
                $this->form->delete();
                flash()->success('Tag deleted successfully.');

                Log::info('Tag deleted successfully', [
                    'user_id' => Auth::id(),
                    'correlation_id' => $this->correlationId,
                    'deleted_tag_name' => $tagName,
                ]);
            }
        } catch (AuthorizationException $e) {
            Log::warning('Unauthorized tag deletion', ['user_id' => Auth::id()]);
            flash()->error('You are not authorized to delete tags.');
        } catch (\Exception $e) {
            Log::error('Tag deletion failed', [
                'user_id' => Auth::id(),
                'correlation_id' => $this->correlationId,
                'error' => $e->getMessage(),
            ]);
            flash()->error("An error occurred while deleting the tag. Please try again later. #{$this->correlationId}");
        } finally {
            $this->dispatch('close-modal', 'delete-tag-confirmation');
            $this->editId = null;
            $this->deleteId = null;
        }
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.articles.tags.index');

        $tags = ModelsTag::withCount('articles')
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('slug', 'like', '%' . $this->search . '%');
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.article.tag', [
            'tags' => $tags,
            'table_heads' => $this->table_heads,
        ]);
    }
}
