<?php

namespace App\Modules\LegalDocuments\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class LegalDocument extends Model
{
    use BelongsToSchool;

    protected $table = 'legal_documents';

    protected $fillable = ['school_id', 'title', 'file_path', 'uploaded_by'];

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function signatures()
    {
        return $this->hasMany(LegalDocumentSignature::class);
    }
}
