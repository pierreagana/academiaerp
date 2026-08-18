<?php

namespace App\Modules\Transport\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use SoftDeletes;

    protected $table = 'transport_drivers';

    protected $fillable = [
        'school_id',
        'first_name',
        'last_name',
        'password',
        'id_card_front',
        'id_card_back',
        'license_front',
        'license_back',
        'has_assistant',
        'assistant_name',
        'assistant_id_card_front',
        'assistant_id_card_back',
    ];

    protected $casts = [
        'has_assistant' => 'boolean',
    ];

    protected $hidden = [
        'password',
    ];

    public function buses()
    {
        return Bus::where('driver_type', 'driver')->where('driver_id', $this->id);
    }
}
