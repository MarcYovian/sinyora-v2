<?php

namespace App\Livewire\Admin\Pages\Event;

use App\Enums\EventApprovalStatus;
use App\Livewire\Forms\EventForm;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\Location;
use App\Models\Organization;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    public EventForm $form;
    public ?int $editId = null;
    public ?int $deleteId = null;
    public ?int $approveId = null;
    public ?int $rejectId = null;

    public Collection $categories;
    public Collection $organizations;
    public Collection $locations;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 's')]
    public string $filterStatus = '';

    public string $correlationId = '';

    /**
     * Mount the component and load dropdown data.
     */
    public function mount(): void
    {
        $this->correlationId = Str::uuid()->toString();

        $this->authorize('access', 'admin.events.index');

        $this->categories = Cache::remember('event_categories_dropdown', 3600, function () {
            return EventCategory::active()->get(['id', 'name']);
        });
        $this->organizations = Cache::remember('organizations_dropdown', 3600, function () {
            return Organization::active()->get(['id', 'name']);
        });
        $this->locations = Cache::remember('locations_dropdown', 3600, function () {
            return Location::active()->get(['id', 'name']);
        });
    }

    /**
     * Handle property updates (reset pagination on filter change).
     */
    public function updated(string $propertyName): void
    {
        if (in_array($propertyName, ['search', 'filterStatus'])) {
            $this->resetPage();
        }
    }

    /**
     * Open modal for creating a new event.
     */
    public function create(): void
    {
        $this->authorize('access', 'admin.events.create');

        $this->form->reset();
        $this->editId = null;
        $this->deleteId = null;
        $this->dispatch('open-modal', 'event-modal');

        Log::debug('Create event modal opened', [
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Open modal for editing an existing event.
     */
    public function edit(int $id): void
    {
        $this->authorize('access', 'admin.events.edit');

        $this->form->reset();
        $this->editId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'event-modal');

        Log::debug('Edit event modal opened', [
            'event_id' => $id,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);
    }

    /**
     * Save (create or update) an event.
     */
    public function save(): void
    {
        try {
            if ($this->editId) {
                $this->authorize('access', 'admin.events.edit');

                Log::info('Updating event', [
                    'event_id' => $this->editId,
                    'user_id' => auth()->id(),
                    'correlation_id' => $this->correlationId,
                ]);

                $this->form->update();
                $this->editId = null;
                flash()->success(__('Event updated successfully.'));

                Log::info('Event updated successfully', [
                    'event_id' => $this->editId,
                    'correlation_id' => $this->correlationId,
                ]);
            } else {
                $this->authorize('access', 'admin.events.create');

                Log::info('Creating event', [
                    'user_id' => auth()->id(),
                    'correlation_id' => $this->correlationId,
                ]);

                $this->form->store();
                flash()->success(__('Event created successfully.'));

                Log::info('Event created successfully', [
                    'correlation_id' => $this->correlationId,
                ]);
            }
            $this->dispatch('close-modal', 'event-modal');
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk operasi ini.');
            Log::warning('Unauthorized event save attempt', [
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            // Let validation errors bubble up to form
            throw $e;
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Event save failed', [
                'event_id' => $this->editId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        }
    }

    /**
     * Open delete confirmation modal.
     */
    public function confirmDelete(int $id): void
    {
        $this->authorize('access', 'admin.events.destroy');

        $this->deleteId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'delete-event-confirmation');
    }

    /**
     * Delete an event.
     */
    public function delete(): void
    {
        Log::info('Event deletion initiated', [
            'event_id' => $this->deleteId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.events.destroy');

            if (!$this->deleteId) {
                return;
            }

            $this->form->delete();

            flash()->success(__('Event deleted successfully.'));
            $this->dispatch('close-modal', 'delete-event-confirmation');

            Log::info('Event deleted successfully', [
                'event_id' => $this->deleteId,
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menghapus event.');
            Log::warning('Unauthorized event deletion attempt', [
                'event_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Event deletion failed', [
                'event_id' => $this->deleteId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->deleteId = null;
        }
    }

    /**
     * Open approve confirmation modal.
     */
    public function confirmApprove(int $id): void
    {
        $this->authorize('access', 'admin.events.approve');
        $this->form->resetErrorBag();
        $this->approveId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'approve-event-confirmation');
    }

    /**
     * Approve an event.
     */
    public function approve(): void
    {
        Log::info('Event approval initiated', [
            'event_id' => $this->approveId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.events.approve');

            if (!$this->approveId) {
                return;
            }

            $this->form->approve();

            flash()->success('Event approved successfully.');
            $this->dispatch('close-modal', 'approve-event-confirmation');

            Log::info('Event approved successfully', [
                'event_id' => $this->approveId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menyetujui event.');
            Log::warning('Unauthorized event approval attempt', [
                'event_id' => $this->approveId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
            Log::warning('Event approval validation failed', [
                'event_id' => $this->approveId,
                'error' => $e->validator->errors()->first(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Event approval failed', [
                'event_id' => $this->approveId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->approveId = null;
        }
    }

    /**
     * Open reject confirmation modal.
     */
    public function confirmReject(int $id): void
    {
        $this->authorize('access', 'admin.events.reject');

        $this->rejectId = $id;
        $this->form->setEvent($id);
        $this->dispatch('open-modal', 'reject-event-confirmation');
    }

    /**
     * Reject an event.
     */
    public function reject(): void
    {
        Log::info('Event rejection initiated', [
            'event_id' => $this->rejectId,
            'user_id' => auth()->id(),
            'correlation_id' => $this->correlationId,
        ]);

        try {
            $this->authorize('access', 'admin.events.reject');

            if (!$this->rejectId) {
                return;
            }

            $this->form->reject();

            flash()->success('Event rejected successfully.');
            $this->dispatch('close-modal', 'reject-event-confirmation');

            Log::info('Event rejected successfully', [
                'event_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (AuthorizationException $e) {
            flash()->error('Anda tidak memiliki izin untuk menolak event.');
            Log::warning('Unauthorized event rejection attempt', [
                'event_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (ValidationException $e) {
            flash()->error($e->validator->errors()->first());
            Log::warning('Event rejection validation failed', [
                'event_id' => $this->rejectId,
                'error' => $e->validator->errors()->first(),
                'correlation_id' => $this->correlationId,
            ]);
        } catch (\Exception $e) {
            flash()->error("Terjadi kesalahan yang tidak terduga. #{$this->correlationId}");
            Log::error('Event rejection failed', [
                'event_id' => $this->rejectId,
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'correlation_id' => $this->correlationId,
            ]);
        } finally {
            $this->rejectId = null;
        }
    }

    /**
     * Add a custom schedule entry to the form.
     */
    public function addCustomSchedule(): void
    {
        $this->form->addCustomSchedule();
    }

    /**
     * Remove a custom schedule entry from the form.
     */
    public function removeCustomSchedule(int $index): void
    {
        $this->form->removeCustomSchedule($index);
    }

    /**
     * Reset all filters.
     */
    public function resetFilters(): void
    {
        $this->reset('search', 'filterStatus');
        $this->resetPage();
    }

    /**
     * Render the component.
     */
    public function render()
    {
        $this->authorize('access', 'admin.events.index');

        $table_heads = ['No', 'Event', 'Period', 'Categories', 'Locations', 'Status', 'Action'];

        $events = Event::query()
            ->select([
                'id',
                'name',
                'start_recurring',
                'end_recurring',
                'status',
                'recurrence_type',
                'event_category_id',
                'organization_id',
                'creator_id',
                'creator_type',
                'created_at',
            ])
            ->with([
                'eventCategory:id,name',
                'organization:id,name',
                'locations:locations.id,locations.name',
                'eventRecurrences:id,event_id,date,time_start,time_end',
            ])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhereHas('eventCategory', function ($sub) {
                            $sub->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('organization', function ($sub) {
                            $sub->where('name', 'like', '%' . $this->search . '%');
                        })
                        ->orWhereHas('locations', function ($sub) {
                            $sub->where('name', 'like', '%' . $this->search . '%');
                        });
                });
            })
            ->when($this->filterStatus, function ($query) {
                $query->where('status', $this->filterStatus);
            })
            ->latest()
            ->paginate(10);

        return view('livewire.admin.pages.event.index', [
            'table_heads' => $table_heads,
            'events' => $events
        ]);
    }
}
