<?php

namespace App\Modules\LegalDocuments\Domain\Models;

use App\Modules\Academic\Domain\Models\ParentAccount;
use Illuminate\Database\Eloquent\Model;

class LegalDocumentSignature extends Model
{
    protected $table = 'legal_document_signatures';

    protected $fillable = ['legal_document_id', 'parent_id', 'signed_at'];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function legalDocument()
    {
        return $this->belongsTo(LegalDocument::class);
    }

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }
}
