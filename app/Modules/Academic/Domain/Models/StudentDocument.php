<?php

namespace App\Modules\Academic\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class StudentDocument extends Model
{
    use BelongsToSchool;
    public const STATUS_PENDING = 'pending';
    public const STATUS_VALIDATED = 'validated';
    public const STATUS_REJECTED = 'rejected';

    public const TYPES = [
        'birth_certificate' => 'Extrait / Acte de naissance',
        'enrollment_certificate' => 'Certificat de scolarité',
        'previous_report_card' => 'Bulletin précédent',
        'id_photo' => "Photo d'identité",
        'medical_certificate' => 'Certificat médical',
        'transfer_document' => 'Document de transfert',
        'guardian_id' => 'Pièce du parent/tuteur',
        'other' => 'Autre document',
    ];

    protected $fillable = [
        'school_id', 'student_id', 'type', 'label', 'file_path', 'deposited_at', 'status', 'uploaded_by',
    ];

    protected $casts = [
        'deposited_at' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
