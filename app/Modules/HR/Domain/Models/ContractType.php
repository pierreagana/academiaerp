<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class ContractType extends Model
{
    use BelongsToSchool;
    protected $table = 'hr_contract_types';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
