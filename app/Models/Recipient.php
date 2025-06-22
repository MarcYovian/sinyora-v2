<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recipient extends Model
{
    protected $fillable = [
        'invitation_document_id',
        'recipient',
        'recipient_position',
    ];
    public function invitationDocument()
    {
        return $this->belongsTo(InvitationDocument::class);
    }
}
