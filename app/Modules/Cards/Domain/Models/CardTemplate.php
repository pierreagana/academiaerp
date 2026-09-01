<?php

namespace App\Modules\Cards\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class CardTemplate extends Model
{
    use BelongsToSchool;
    protected $table = 'card_templates';

    public const TYPES = [
        'student' => 'Carte Étudiant',
        'staff' => 'Carte Personnel',
    ];

    public const PHOTO_POSITIONS = [
        'left' => 'Aligné à Gauche',
        'center' => 'Centré',
        'right' => 'Aligné à Droite',
    ];

    public const COLOR_SWATCHES = ['#031C5B', '#7C3AED', '#059669', '#DC2626'];

    public const ORIENTATIONS = [
        'portrait' => 'Portrait',
        'landscape' => 'Paysage',
    ];

    public const STUDENT_FIELDS = [
        'full_name' => 'Nom Complet',
        'student_id' => 'Matricule',
        'class' => 'Classe',
        'blood_group' => 'Groupe Sanguin',
        'academic_year' => 'Année Scolaire',
        'date_of_birth' => 'Date de Naissance',
        'emergency_contact' => "Contact d'Urgence",
    ];

    public const STAFF_FIELDS = [
        'full_name' => 'Nom Complet',
        'employee_id' => 'Matricule',
        'department' => 'Département',
        'role' => 'Poste',
        'phone' => 'Téléphone',
        'hire_date' => "Date d'Embauche",
        'contract_type' => 'Type de Contrat',
    ];

    protected $fillable = [
        'school_id',
        'card_type',
        'logo_path',
        'watermark_path',
        'primary_color',
        'background_color',
        'text_color',
        'photo_position',
        'orientation',
        'front_fields',
        'back_fields',
    ];

    protected $casts = [
        'front_fields' => 'array',
        'back_fields' => 'array',
    ];

    public function fieldCatalog(): array
    {
        return $this->card_type === 'staff' ? self::STAFF_FIELDS : self::STUDENT_FIELDS;
    }

    public static function defaultsFor(string $type): array
    {
        return $type === 'staff'
            ? [
                'front_fields' => ['full_name', 'employee_id', 'department', 'role'],
                'back_fields' => ['phone', 'hire_date', 'contract_type'],
            ]
            : [
                'front_fields' => ['full_name', 'student_id', 'class', 'blood_group'],
                'back_fields' => ['emergency_contact', 'date_of_birth', 'academic_year'],
            ];
    }
}
