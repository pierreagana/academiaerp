<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'type',
        'status',
        'plan_name',
        'subscription_renewal_date',
        'students_count',
        'storage_used_gb',
        'location',
        'contact_email',
        'contact_phone',
        'founded_date',
        'domain',
        'theme_color',
        'logo_url',
        'slogan',
        'latitude',
        'longitude',
        'description',
        'levels',
        'tags',
        'facilities',
        'gallery_paths',
        'success_rate',
        'academic_radar',
        'nearby_places',
    ];

    protected $casts = [
        'founded_date'   => 'date',
        'storage_used_gb' => 'decimal:2',
        'subscription_renewal_date' => 'date',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'levels' => 'array',
        'tags' => 'array',
        'facilities' => 'array',
        'gallery_paths' => 'array',
        'success_rate' => 'integer',
        'academic_radar' => 'array',
        'nearby_places' => 'array',
    ];

    /** Facility keys the School Track discovery UI displays — self-declared by the school, not inferred. */
    public const FACILITY_KEYS = [
        'wifi', 'energie_solaire', 'laboratoire', 'informatique', 'piscine', 'internat', 'sport', 'cantine',
    ];

    /** Subject categories the school self-rates (0-100) for the School Track comparison radar. */
    public const ACADEMIC_RADAR_KEYS = ['Sciences', 'Maths', 'Lettres', 'Langues'];

    public const SCHOOL_TRACK_LEVELS = ['Maternelle', 'Primaire', 'Collège', 'Lycée'];

    public function branches()
    {
        return $this->hasMany(\App\Modules\Academic\Domain\Models\Branch::class);
    }

    public function extensionRequests()
    {
        return $this->hasMany(SchoolExtensionRequest::class);
    }

    /**
     * The real SaaS package this school is subscribed to, matched by name.
     * Returns null if plan_name is empty or doesn't match a real package
     * (e.g. legacy/placeholder plan names) — callers must treat that as
     * "no restrictions to enforce", not as an error.
     */
    public function activePackage(): ?SaasPackage
    {
        if (empty($this->plan_name)) {
            return null;
        }

        return SaasPackage::where('name', $this->plan_name)->first();
    }

    /** Module names this school has been granted access to via an approved paid extension. */
    public function approvedExtensionModuleNames(): array
    {
        return $this->extensionRequests()->where('status', 'approved')->pluck('module_name')->all();
    }

    /**
     * Module names this school actually has access to (package features +
     * approved extensions). Returns null when there's no real package to
     * restrict against — callers must treat that as "every module is
     * accessible", matching the fail-open behaviour of schoolHasModuleAccess().
     */
    public function accessibleModuleNames(): ?array
    {
        $package = $this->activePackage();
        if (!$package) {
            return null;
        }

        $features = is_array($package->features) ? $package->features : [];

        return array_values(array_unique(array_merge($features, $this->approvedExtensionModuleNames())));
    }

    /**
     * A school only appears in parent-facing School Track discovery once it
     * has filled in its own profile — computed live from real columns
     * (nothing stored/denormalized), so there's no separate flag to keep in
     * sync. Deliberately excludes `tags`/`nearby_places` (nice-to-have, not
     * required) — required fields are the ones the comparison/detail UI
     * can't render meaningfully without.
     */
    public function isSchoolTrackProfileComplete(): bool
    {
        if (empty($this->description) || empty($this->levels) || empty($this->gallery_paths)) {
            return false;
        }

        if ($this->success_rate === null) {
            return false;
        }

        $facilities = is_array($this->facilities) ? $this->facilities : [];
        if (empty(array_filter($facilities))) {
            return false;
        }

        $radar = is_array($this->academic_radar) ? $this->academic_radar : [];
        foreach (self::ACADEMIC_RADAR_KEYS as $key) {
            if (!isset($radar[$key])) {
                return false;
            }
        }

        return true;
    }

    /** Real teacher headcount for this school — no relation exists on Teacher, so queried directly by school_id. */
    public function teacherCount(): int
    {
        return \App\Modules\Academic\Domain\Models\Teacher::where('school_id', $this->id)->count();
    }

    /** "1:23" ratio label from real teacher/student headcounts, or null when there's nothing to divide. */
    public function teacherStudentRatioLabel(): ?string
    {
        $teachers = $this->teacherCount();
        if ($teachers === 0 || $this->students_count <= 0) {
            return null;
        }

        return '1:' . (int) round($this->students_count / $teachers);
    }

    /**
     * Real average annual tuition for the current academic year, derived
     * from this school's own FeeLevel rows (tuition varies per grade level,
     * so this averages across whatever levels are configured) — null when
     * the school hasn't configured any tuition fee levels yet, never a
     * fabricated number.
     */
    public function averageAnnualTuitionFee(): ?float
    {
        $currentYear = now()->month >= 8
            ? now()->year . '-' . (now()->year + 1)
            : (now()->year - 1) . '-' . now()->year;

        $levels = \App\Modules\Finance\Domain\Models\FeeLevel::where('school_id', $this->id)
            ->where('type', 'tuition')
            ->where('academic_year', $currentYear)
            ->get();

        if ($levels->isEmpty()) {
            return null;
        }

        return round($levels->avg(fn ($level) => $level->total_amount), 2);
    }
}
