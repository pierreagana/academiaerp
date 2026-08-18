<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffMember extends Model
{
    use HasFactory;

    protected $table = 'staff_members';

    protected $fillable = [
        'staff_code',
        'name',
        'email',
        'role',
        'department',
        'status',
        'last_login',
    ];
}
