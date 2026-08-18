<?php

namespace App\Modules\ReportCard\Domain\Models;

use Illuminate\Database\Eloquent\Model;

class ReportCardCompetency extends Model
{
    protected $table = 'report_card_competencies';

    protected $fillable = ['subdomain_id', 'statement'];

    public function subdomain()
    {
        return $this->belongsTo(ReportCardSubdomain::class, 'subdomain_id');
    }

    public function assessments()
    {
        return $this->hasMany(ReportCardAssessment::class, 'competency_id');
    }
}
