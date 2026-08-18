<?php

namespace App\Modules\Finance\Domain\Models;

use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Teacher;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'school_id',
        'reference',
        'title',
        'amount',
        'category',
        'payee',
        'expense_date',
        'status',
        'proof_path',
        'note',
        'teacher_id',
        'staff_id',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public const STATUSES = [
        'pending' => 'En attente',
        'approved' => 'Validé',
        'rejected' => 'Rejeté',
    ];

    public const CATEGORY_ICONS = [
        'Salaires' => 'ph-money',
        'Fournitures' => 'ph-package',
        'Maintenance' => 'ph-wrench',
        'Informatique' => 'ph-laptop',
        'Utilités' => 'ph-lightning',
        'Événements' => 'ph-confetti',
        'Entretien & Locaux' => 'ph-paint-roller',
        'Divers' => 'ph-dots-three-circle',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    public static function iconFor(string $category): string
    {
        return self::CATEGORY_ICONS[$category] ?? 'ph-receipt';
    }
}
