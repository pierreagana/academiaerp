<?php

namespace App\Modules\Infirmary\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use App\Support\Tenancy\BelongsToSchool;

class Medication extends Model
{
    use HasFactory, SoftDeletes, BelongsToSchool;

    protected $table = 'infirmary_medications';

    protected $fillable = [
        'school_id',
        'name',
        'category',
        'quantity',
        'max_quantity',
        'low_stock_threshold',
        'critical_threshold',
        'expiry_date',
    ];

    protected $casts = [
        'expiry_date' => 'date',
    ];

    public function movements()
    {
        return $this->hasMany(MedicationMovement::class);
    }

    public function getStatusAttribute(): string
    {
        if ($this->critical_threshold !== null && $this->quantity <= $this->critical_threshold) {
            return 'critical';
        }

        $expiringSoon = $this->expiry_date && $this->expiry_date->lte(Carbon::today()->addDays(60));

        if (($this->low_stock_threshold !== null && $this->quantity <= $this->low_stock_threshold) || $expiringSoon) {
            return 'to_monitor';
        }

        return 'optimal';
    }
}
