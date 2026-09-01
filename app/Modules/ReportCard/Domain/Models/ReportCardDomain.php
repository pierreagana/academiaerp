<?php

namespace App\Modules\ReportCard\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use App\Support\Tenancy\BelongsToSchool;

class ReportCardDomain extends Model
{
    use BelongsToSchool;
    protected $table = 'report_card_domains';

    protected $fillable = ['school_id', 'cycle', 'name'];

    public function subdomains()
    {
        return $this->hasMany(ReportCardSubdomain::class, 'domain_id');
    }
}
