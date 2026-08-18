<?php

namespace App\Modules\ReportCard\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardDomain extends Model
{
    protected $table = 'report_card_domains';

    protected $fillable = ['school_id', 'cycle', 'name'];

    public function subdomains()
    {
        return $this->hasMany(ReportCardSubdomain::class, 'domain_id');
    }
}
