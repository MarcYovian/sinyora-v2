<?php

namespace App\Livewire\Admin\Pages\Document;

use App\Models\InvitationDocument;
use Livewire\Attributes\On;
use Livewire\Component;

class InvitationDocumentModal extends Component
{

    public $documentData;

    #[On('setDataFormInvitationModal')]
    public function setData($data)
    {
        $this->documentData = $data;
    }

    public function render()
    {
        return view('livewire.admin.pages.document.invitation-document-modal');
    }
}
