<?php

namespace App\Support\Tenancy;

trait BelongsToSchool
{
    public static function bootBelongsToSchool(): void
    {
        static::addGlobalScope(new SchoolScope);

        static::creating(function ($model) {
            if (empty($model->school_id)) {
                $schoolId = CurrentTenant::schoolId();

                if ($schoolId !== null) {
                    $model->school_id = $schoolId;
                }
            }
        });
    }

    /**
     * Explicit escape hatch for legitimate cross-tenant reads
     * (e.g. a future superadmin "view as school X" flow).
     */
    public function scopeWithoutSchoolScope($query)
    {
        return $query->withoutGlobalScope(SchoolScope::class);
    }
}
