<?php

namespace App\Modules\ReportCard\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardSubdomain extends Model
{
    protected $table = 'report_card_subdomains';

    protected $fillable = ['domain_id', 'name'];

    public function domain()
    {
        return $this->belongsTo(ReportCardDomain::class, 'domain_id');
    }

    public function competencies()
    {
        return $this->hasMany(ReportCardCompetency::class, 'subdomain_id');
    }
}
