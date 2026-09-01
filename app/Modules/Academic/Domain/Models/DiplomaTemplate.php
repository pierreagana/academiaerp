<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class DiplomaTemplate extends Model
{
    use BelongsToSchool;
    public const BORDER_STYLES = [
        'classic' => 'Classique (double liseré)',
        'modern' => 'Moderne (liseré simple)',
        'none' => 'Sans bordure',
    ];

    public const ORIENTATIONS = [
        'landscape' => 'Paysage',
        'portrait' => 'Portrait',
    ];

    public const LAYOUTS = [
        'classic' => 'Classique',
        'modern' => 'Moderne',
        'elegant' => 'Élégant',
    ];

    public const DEFAULT_BODY_TEXT = "En reconnaissance de {award} décerné(e) à {recipient} le {date}.\n\nMotif : {reason}";

    /** Optional data points the admin can check on/off and freely position on the diploma canvas. */
    public const AVAILABLE_FIELDS = [
        'recipient_name' => 'Nom complet',
        'recipient_first_name' => 'Prénom',
        'recipient_last_name' => 'Nom de famille',
        'age' => 'Âge',
        'date_of_birth' => 'Date de naissance',
        'class_name' => 'Classe',
        'matricule' => 'Matricule',
        'award_name' => 'Récompense',
        'award_category' => 'Catégorie',
        'reason' => 'Motif',
        'awarded_date' => "Date d'attribution",
        'school_name' => 'École',
    ];

    /** Images that can be freely dragged on the canvas, positioned the same way as AVAILABLE_FIELDS entries. */
    public const POSITIONABLE_MEDIA = [
        'logo' => "Logo de l'École",
        'seal' => 'Sceau / Cachet',
    ];

    protected $fillable = [
        'school_id', 'award_type_id', 'title', 'subtitle', 'body_text', 'orientation', 'border_style', 'layout',
        'primary_color', 'background_color', 'text_color', 'logo_path', 'seal_path', 'background_image_path',
        'signature_1_name', 'signature_1_title', 'signature_2_name', 'signature_2_title', 'fields_layout',
    ];

    protected $casts = [
        'fields_layout' => 'array',
    ];

    public function awardType()
    {
        return $this->belongsTo(AwardType::class);
    }

    /** The school's default/global template (award_type_id null) — auto-created on first access. */
    public static function findOrDefault(int $schoolId): self
    {
        $template = self::where('school_id', $schoolId)->whereNull('award_type_id')->first();

        if (!$template) {
            $template = self::create([
                'school_id' => $schoolId,
                'body_text' => self::DEFAULT_BODY_TEXT,
            ]);
        }

        return $template;
    }

    /** A specific award model's own diploma design, if one has been configured — never auto-created. */
    public static function forAwardType(int $schoolId, int $awardTypeId): ?self
    {
        return self::where('school_id', $schoolId)->where('award_type_id', $awardTypeId)->first();
    }

    /** Used to actually print a diploma: the award's own template if it has one, else the school's default. */
    public static function resolveFor(int $schoolId, ?int $awardTypeId): self
    {
        if ($awardTypeId) {
            $specific = self::forAwardType($schoolId, $awardTypeId);
            if ($specific) {
                return $specific;
            }
        }

        return self::findOrDefault($schoolId);
    }

    /**
     * $fieldValues is the full AVAILABLE_FIELDS-keyed array (e.g. from a
     * controller's resolveFieldValues()). Every field is usable as a
     * {field_key} variable in body_text, plus the original short aliases
     * ({recipient}, {award}, {reason}, {date}, {school}) kept for
     * backward compatibility with templates saved before this existed.
     */
    public function render(array $fieldValues): string
    {
        $body = $this->body_text ?: self::DEFAULT_BODY_TEXT;

        $aliases = [
            'recipient' => $fieldValues['recipient_name'] ?? '',
            'award' => $fieldValues['award_name'] ?? '',
            'reason' => $fieldValues['reason'] ?? '',
            'date' => $fieldValues['awarded_date'] ?? '',
            'school' => $fieldValues['school_name'] ?? '',
        ];

        $replacements = [];
        foreach (array_merge($aliases, $fieldValues) as $key => $value) {
            $replacements['{' . $key . '}'] = $value;
        }

        return strtr($body, $replacements);
    }
}
