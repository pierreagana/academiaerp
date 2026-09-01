<?php

namespace App\Modules\Transport\Domain\Models;

use App\Models\User;
use App\Modules\Academic\Domain\Models\Student;
use Illuminate\Database\Eloquent\Model;

class TransportBoardingScan extends Model
{
    public const ACTIONS = [
        'board' => 'Montée',
        'alight' => 'Descente',
    ];

    protected $fillable = [
        'student_id',
        'bus_id',
        'route_id',
        'period',
        'action',
        'latitude',
        'longitude',
        'address',
        'client_scan_id',
        'scanned_at',
        'scanned_by_user_id',
        'scanned_by_device_id',
        'scanned_by_driver_id',
    ];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function bus()
    {
        return $this->belongsTo(Bus::class);
    }

    public function route()
    {
        return $this->belongsTo(Route::class);
    }

    public function scannedBy()
    {
        return $this->belongsTo(User::class, 'scanned_by_user_id');
    }

    public function scannedByDriver()
    {
        return $this->belongsTo(Driver::class, 'scanned_by_driver_id');
    }
}
