<?php

namespace App\Modules\Presence\Domain\Models;

use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Route;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use App\Support\Tenancy\BelongsToSchool;

class AccessDevice extends Authenticatable
{
    use HasApiTokens, BelongsToSchool;

    public const TYPES = [
        'portal_entry' => 'Portail — Entrée',
        'portal_exit' => 'Portail — Sortie',
        'canteen' => 'Accès Cantine',
        'bus_board' => 'Bus — Montée',
        'bus_alight' => 'Bus — Descente',
    ];

    protected $fillable = [
        'school_id',
        'branch_id',
        'name',
        'password',
        'access_type',
        'access_point_id',
        'bus_id',
        'route_id',
        'is_active',
        'last_used_at',
    ];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_used_at' => 'datetime',
        ];
    }

    /** Human-readable, read-only label for what this device is bound to — the app displays this, never lets it be edited. */
    public function getLabelAttribute(): string
    {
        return match ($this->access_type) {
            'portal_entry', 'portal_exit' => $this->accessPoint?->name ?? 'Portail',
            'canteen' => $this->accessPoint?->name ?? 'Cantine',
            'bus_board', 'bus_alight' => trim(($this->bus?->bus_number ?? 'Bus') . ($this->route ? ' — ' . $this->route->name : '')),
            default => $this->access_type,
        };
    }

    public function accessPoint()
    {
        return $this->belongsTo(AccessPoint::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }
}
