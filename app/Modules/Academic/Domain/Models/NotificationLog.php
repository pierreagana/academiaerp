<?php

namespace App\Modules\Academic\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $table = 'notifications_log';

    public const TYPES = [
        'attendance' => 'Présence',
        'bus' => 'Transport',
        'infirmary' => 'Infirmerie',
        'fee' => 'Scolarité',
        'canteen' => 'Cantine',
        'homework' => 'Devoirs',
        'bulletin' => 'Bulletin',
        'library' => 'Bibliothèque',
        'payment' => 'Paiement',
    ];

    protected $fillable = ['parent_id', 'student_id', 'type', 'title', 'body', 'read_at'];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(ParentAccount::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
