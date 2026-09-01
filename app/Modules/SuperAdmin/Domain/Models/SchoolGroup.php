<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class SchoolGroup extends Model
{
    protected $fillable = ['name', 'founder_user_id'];

    public function founder()
    {
        return $this->belongsTo(User::class, 'founder_user_id');
    }

    public function schools()
    {
        return $this->hasMany(School::class);
    }
}
