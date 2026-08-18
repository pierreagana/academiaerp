<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Room extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'school_id',
        'building_id',
        'name',
        'capacity',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class, 'building_id');
    }
}
