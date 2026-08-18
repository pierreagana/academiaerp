<?php

namespace App\Modules\HR\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ContractType extends Model
{
    protected $table = 'hr_contract_types';

    protected $fillable = [
        'school_id',
        'name',
    ];
}
