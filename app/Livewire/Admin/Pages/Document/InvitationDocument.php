<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\InvitationDocument as ModelsInvitationDocument;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

class InvitationDocument extends Component
{
    use WithPagination, AuthorizesRequests;

    #[Layout('layouts.app')]

    // public EventCategoryForm $form;
    public $search = '';

    public function view(ModelsInvitationDocument $document)
    {
        $document->load(['documents', 'recipients', 'schedules']);
        $this->dispatch('open-modal', 'invitation-document-detail-modal');
        $this->dispatch('setDataFormInvitationModal', data: $document->toArray())->to(InvitationDocumentModal::class);
    }

    public function render()
    {
        $table_heads = ['#', 'Event', 'Date', 'Time', 'location', 'Actions'];

        $documents = ModelsInvitationDocument::paginate(10);

        $documents->through(function ($document) {
            $startDate = Carbon::parse($document->start_datetime);
            $endDate = Carbon::parse($document->end_datetime);

            // Determine date display
            if ($startDate->isSameDay($endDate)) {
                $formattedDate = $startDate->isoFormat('dddd, D MMMM YYYY');
            } else {
                $formattedDate = $startDate->isoFormat('dddd, D MMMM YYYY') . ' - ' . $endDate->isoFormat('dddd, D MMMM YYYY');
            }

            // Determine time display
            $formattedTime = $startDate->isoFormat('HH:mm') . ' - ' . $endDate->isoFormat('HH:mm') . ' WIB';

            $document->formatted_date = $formattedDate;
            $document->formatted_time = $formattedTime;

            return $document;
        });

        return view('livewire.admin.pages.document.invitation-document', [
            'table_heads' => $table_heads,
            'documents' => $documents,
        ]);
    }
}
