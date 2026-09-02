<?php

namespace App\Modules\SuperAdmin\Domain\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class School extends Model
{
    use SoftDeletes;

    protected static function boot()
    {
        parent::boot();

        // `code` is what identifies the school in the ID-card QR codes
        // ("{code}:{matricule}", see CardController::printCards and
        // MobileParentController::studentProfile) — every school must have
        // one, regardless of which flow creates it (SuperAdmin form,
        // registration approval, seeders...).
        static::creating(function (School $school) {
            if (empty($school->code)) {
                $school->code = self::generateUniqueCode($school->name ?? '');
            }
        });
    }

    public static function generateUniqueCode(string $name): string
    {
        $letters = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $prefix = str_pad(substr($letters, 0, 3) ?: 'ECO', 3, 'X');

        do {
            $code = "ACAD-{$prefix}" . random_int(1000, 9999);
        } while (self::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    protected $fillable = [
        'school_group_id',
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
        'city',
        'country',
        'latitude',
        'longitude',
        'description',
        'sector',
        'is_bilingual',
        'language_regime',
        'day_start_time',
        'day_end_time',
        'levels',
        'tags',
        'facilities',
        'gallery_paths',
        'success_rate',
        'progression_annuelle',
        'academic_radar',
        'nearby_places',
        'catalog_paths',
    ];

    /** Max photos allowed in the establishment's own catalog (see `catalog_paths`). */
    public const CATALOG_MAX_PHOTOS = 6;

    protected $casts = [
        'founded_date'   => 'date',
        'storage_used_gb' => 'decimal:2',
        'subscription_renewal_date' => 'date',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'is_bilingual' => 'boolean',
        'levels' => 'array',
        'tags' => 'array',
        'facilities' => 'array',
        'gallery_paths' => 'array',
        'success_rate' => 'integer',
        'progression_annuelle' => 'integer',
        'academic_radar' => 'array',
        'nearby_places' => 'array',
        'catalog_paths' => 'array',
    ];

    /** Default options configured by SuperAdmin */
    public const DEFAULT_SECTORS = ['Privé', 'Public', 'Semi-privé'];
    public const DEFAULT_LEVELS = ['Préscolaire', 'Primaire', 'Collège', 'Lycée'];
    public const DEFAULT_LANGUAGE_REGIMES = ['Monolingue (Français)', 'Bilingue (Français / Anglais)', 'International / Trilingue'];

    public static function getAvailableSectors(): array
    {
        $setting = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'school_sectors')->first();
        if ($setting && !empty($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return self::DEFAULT_SECTORS;
    }

    public static function getAvailableLevels(): array
    {
        $setting = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'school_education_levels')->first();
        if ($setting && !empty($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return self::DEFAULT_LEVELS;
    }

    public static function getAvailableLanguageRegimes(): array
    {
        $setting = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'school_language_regimes')->first();
        if ($setting && !empty($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return self::DEFAULT_LANGUAGE_REGIMES;
    }

    /** "2026-2027" style label for the school year in progress right now, school-calendar-aware (rentrée in August). */
    public static function currentAcademicYear(): string
    {
        $now = now();
        $startYear = (int) $now->format('n') >= 8 ? (int) $now->format('Y') : (int) $now->format('Y') - 1;

        return $startYear . '-' . ($startYear + 1);
    }

    /** Rolling 2-years-back/2-years-forward window around the current school year — used when no SuperAdmin override is configured, so the list never goes stale like a fixed constant would. */
    public static function defaultAcademicYears(): array
    {
        $currentStartYear = (int) explode('-', self::currentAcademicYear())[0];

        return collect(range($currentStartYear - 2, $currentStartYear + 2))
            ->map(fn ($y) => $y . '-' . ($y + 1))
            ->all();
    }

    public static function getAvailableAcademicYears(): array
    {
        $setting = \App\Modules\SuperAdmin\Domain\Models\GlobalSetting::where('key', 'school_academic_years')->first();
        if ($setting && !empty($setting->value)) {
            $decoded = json_decode($setting->value, true);
            if (is_array($decoded) && !empty($decoded)) {
                return $decoded;
            }
        }
        return self::defaultAcademicYears();
    }

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

    public function group()
    {
        return $this->belongsTo(SchoolGroup::class, 'school_group_id');
    }

    public function facilitiesList()
    {
        return $this->belongsToMany(Facility::class, 'facility_school', 'school_id', 'facility_id')->withTimestamps();
    }

    public function extensionRequests()
    {
        return $this->hasMany(SchoolExtensionRequest::class);
    }

    public function planChangeRequests()
    {
        return $this->hasMany(PlanChangeRequest::class);
    }

    public function wallet()
    {
        return $this->morphOne(\App\Modules\Finance\Domain\Models\Wallet::class, 'owner');
    }

    public function getOrCreateWallet(): \App\Modules\Finance\Domain\Models\Wallet
    {
        return $this->wallet ?? $this->wallet()->create(['balance' => 0, 'currency' => 'XOF']);
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

        // success_rate is no longer self-reported — it's derived from
        // validated exam sessions (see examSuccessRates()). "Complete" now
        // means at least one exam type has a real, validated rate.
        if (empty(array_filter($this->examSuccessRates()))) {
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

    public function examSessions()
    {
        return $this->hasMany(\App\Modules\Academic\Domain\Models\ExamSession::class);
    }

    /** "2025-2026" style label for the academic year right before the current one. */
    public static function previousAcademicYear(?string $academicYear = null): string
    {
        $startYear = (int) explode('-', $academicYear ?? self::currentAcademicYear())[0];

        return ($startYear - 1) . '-' . $startYear;
    }

    /**
     * Which official exams this school can plausibly run a session for right
     * now — i.e. it has at least one active class at that exam's level(s).
     * Drives which exam types the "Nouvelle session" picker offers.
     */
    public function availableExamTypes(): array
    {
        $levelsPresent = $this->academicClasses()->pluck('level')->unique()->all();

        return array_values(array_filter(
            array_keys(\App\Modules\Academic\Domain\Models\ExamSession::LEVELS_BY_TYPE),
            fn ($type) => !empty(array_intersect(\App\Modules\Academic\Domain\Models\ExamSession::levelsForType($type), $levelsPresent))
        ));
    }

    public function academicClasses()
    {
        return $this->hasMany(\App\Modules\Academic\Domain\Models\AcademicClass::class);
    }

    /** Real pass rate (0-100) for one exam type in one year, from the school's own validated session — null when nothing's been validated yet, never fabricated. */
    public function examSuccessRate(string $type, ?string $academicYear = null): ?int
    {
        $session = $this->examSessions()
            ->where('exam_type', $type)
            ->where('academic_year', $academicYear ?? self::currentAcademicYear())
            ->whereNotNull('validated_at')
            ->first();

        return $session?->successRate();
    }

    /** Every exam type's current-year pass rate at once, for the School Track profile — each entry null until that exam's session is validated. */
    public function examSuccessRates(?string $academicYear = null): array
    {
        $rates = [];
        foreach (array_keys(\App\Modules\Academic\Domain\Models\ExamSession::LABELS) as $type) {
            $rates[$type] = $this->examSuccessRate($type, $academicYear);
        }

        return $rates;
    }

    /** Admitted / presented across every validated exam session of a year, all exam types pooled together. */
    public function overallExamAdmissionRate(?string $academicYear = null): ?float
    {
        $sessions = $this->examSessions()
            ->where('academic_year', $academicYear ?? self::currentAcademicYear())
            ->whereNotNull('validated_at')
            ->get();

        $presented = (int) $sessions->sum('presented_count');
        if ($presented <= 0) {
            return null;
        }

        return $sessions->sum('admitted_count') / $presented * 100;
    }

    /**
     * Share of the school's current headcount that was promoted to a higher
     * class this year (real `StudentClassMovement` records ÷ real active
     * student count). The system has no "redoublement" flag, so this is a
     * school-wide activity signal, not a per-cohort graduation rate — still
     * real data, never a fabricated number.
     */
    public function promotionRate(?string $academicYear = null): ?float
    {
        $year = $academicYear ?? self::currentAcademicYear();

        // A school that has never recorded a single promotion hasn't
        // adopted this part of the system yet — null ("no data") reads
        // honestly there, instead of a flat 0% that would look like a real
        // "nobody was promoted" signal.
        $usesPromotionTracking = \App\Modules\Academic\Domain\Models\StudentClassMovement::where('school_id', $this->id)
            ->where('type', \App\Modules\Academic\Domain\Models\StudentClassMovement::TYPE_PROMOTION)
            ->exists();
        if (!$usesPromotionTracking) {
            return null;
        }

        $activeStudents = \App\Modules\Academic\Domain\Models\Student::where('school_id', $this->id)->count();
        if ($activeStudents <= 0) {
            return null;
        }

        $promoted = \App\Modules\Academic\Domain\Models\StudentClassMovement::where('school_id', $this->id)
            ->where('type', \App\Modules\Academic\Domain\Models\StudentClassMovement::TYPE_PROMOTION)
            ->where('to_academic_year', $year)
            ->count();

        return min(100, $promoted / $activeStudents * 100);
    }

    /** Blends promotion activity and exam results into one 0-100 score for a year — whichever of the two is available; null when neither is. */
    public function performanceScore(?string $academicYear = null): ?float
    {
        $parts = array_filter([
            $this->promotionRate($academicYear),
            $this->overallExamAdmissionRate($academicYear),
        ], fn ($v) => $v !== null);

        if (empty($parts)) {
            return null;
        }

        return array_sum($parts) / count($parts);
    }

    /**
     * Real year-over-year progression: this year's performance score minus
     * last year's, in points. Null unless both years have real promotion
     * and/or exam data to compare — never a fabricated trend.
     */
    public function computedProgressionAnnuelle(): ?int
    {
        $current = $this->performanceScore(self::currentAcademicYear());
        $previous = $this->performanceScore(self::previousAcademicYear());

        if ($current === null || $previous === null) {
            return null;
        }

        return (int) round($current - $previous);
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

    /** Coordinates helper */
    /**
     * Real coordinates when the school has them; otherwise a coarse
     * city-level estimate inferred from the real `location` text (still
     * genuine data, just imprecise). Deliberately returns null/null rather
     * than a fabricated point when neither is available — a made-up
     * coordinate would misrepresent the school's position instead of
     * honestly saying it's unknown (see NearbyAmenitiesService::forSchool(),
     * which already avoids this method's old id-based fallback for the
     * same reason).
     */
    public function getCoordinates(): array
    {
        if (!empty($this->latitude) && !empty($this->longitude)) {
            return ['lat' => (float)$this->latitude, 'lng' => (float)$this->longitude];
        }

        $loc = strtolower($this->location ?? '');
        if (str_contains($loc, 'cocody')) return ['lat' => 5.3599, 'lng' => -3.9780];
        if (str_contains($loc, 'plateau')) return ['lat' => 5.3261, 'lng' => -4.0197];
        if (str_contains($loc, 'abidjan')) return ['lat' => 5.3600, 'lng' => -4.0083];
        if (str_contains($loc, 'yamoussoukro')) return ['lat' => 6.8276, 'lng' => -5.2893];
        if (str_contains($loc, 'saint-louis')) return ['lat' => 16.0326, 'lng' => -16.4818];
        if (str_contains($loc, 'dakar')) return ['lat' => 14.7167, 'lng' => -17.4677];
        if (str_contains($loc, 'bamako')) return ['lat' => 12.6392, 'lng' => -8.0029];
        if (str_contains($loc, 'libreville')) return ['lat' => 0.4162, 'lng' => 9.4673];
        if (str_contains($loc, 'yaoundé') || str_contains($loc, 'yaounde')) return ['lat' => 3.8480, 'lng' => 11.5021];

        return ['lat' => null, 'lng' => null];
    }

    public static function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371; // km
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 1);
    }

    /**
     * Turns any of the three shapes a stored image reference can be in into
     * a real, absolute URL — the only thing Image.network() on mobile can
     * load (a relative "/storage/..." URL only works in a browser, which
     * resolves it against the current page's own origin):
     *  - already absolute ("http(s)://...") → returned as-is;
     *  - a legacy "/storage/..." relative URL (from the old bare
     *    `Storage::url()` bug, which used the default 'local' disk instead
     *    of 'public' and so dropped the host) → just add the scheme+host;
     *  - a raw storage-relative path (e.g. "school_catalog/xyz.jpg") → the
     *    normal case, resolved via the 'public' disk.
     */
    public static function absoluteStorageUrl(?string $value): ?string
    {
        if (empty($value)) {
            return null;
        }
        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }
        if (str_starts_with($value, '/storage/')) {
            return rtrim(config('app.url'), '/') . $value;
        }

        return \Illuminate\Support\Facades\Storage::disk('public')->url($value);
    }

    /**
     * Formats the school into the exact shape expected by the Flutter School Track UI.
     * All missing data is returned as null/empty so the UI can explicitly display "Non renseigné".
     */
    public function toSchoolTrackArray(?float $userLat = null, ?float $userLng = null, ?string $userCity = null): array
    {
        $imageUrl = self::absoluteStorageUrl($this->logo_url);

        // Parents just want "photos of the school" — merge School Track's
        // own gallery with the establishment's catalog (whichever the
        // school actually used), deduplicated, absolute URLs only (a
        // relative "/storage/..." URL resolves fine in a browser but is
        // meaningless to Image.network() on mobile).
        $galleryUrls = collect($this->gallery_paths ?? [])
            ->concat($this->catalog_paths ?? [])
            ->map(fn ($path) => self::absoluteStorageUrl($path))
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Facilities mapping dynamically from SuperAdmin Facility model
        $allAdminFacilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)
            ->orderBy('order')
            ->get();
        $schoolFacilitySlugs = [];
        if ($this->relationLoaded('facilitiesList') || $this->facilitiesList()->exists()) {
            $schoolFacilitySlugs = $this->facilitiesList->pluck('slug')->all();
        }
        $rawFacilities = is_array($this->facilities) ? $this->facilities : [];
        $facilitiesMap = [];
        $facilitiesDetails = [];
        foreach ($allAdminFacilities as $f) {
            $hasFacility = in_array($f->slug, $schoolFacilitySlugs, true) || !empty($rawFacilities[$f->slug]);
            $facilitiesMap[$f->slug] = $hasFacility;
            $facilitiesDetails[] = [
                'id'           => (string) $f->id,
                'name'         => $f->name,
                'slug'         => $f->slug,
                'icon'         => $f->icon,
                'category'     => $f->category,
                'description'  => $f->description,
                'is_available' => $hasFacility,
            ];
        }

        // Tags
        $tags = is_array($this->tags) ? $this->tags : [];
        if (empty($tags)) {
            if ($this->is_bilingual) $tags[] = 'Bilingue';
            if ($facilitiesMap['laboratoire'] ?? false) $tags[] = 'Labo';
            if ($facilitiesMap['internat'] ?? false) $tags[] = 'Internat';
            if ($facilitiesMap['sport'] ?? false) $tags[] = 'Sport';
            if ($facilitiesMap['informatique'] ?? false) $tags[] = 'Informatique';
            if ($facilitiesMap['cantine'] ?? false) $tags[] = 'Cantine';
        }

        // Types
        $types = array_values(array_filter([
            $this->sector ?? null,
            $this->is_bilingual ? 'Bilingue' : null,
        ]));

        // Levels (Multiple: e.g. ["Collège", "Lycée", "Primaire"])
        $levels = is_array($this->levels) ? $this->levels : (is_string($this->levels) ? json_decode($this->levels, true) : []);
        if (empty($levels) && !empty($this->type)) {
            $levels = [$this->type];
        }
        $level = !empty($levels) ? implode(' · ', $levels) : ($this->type ?? null);

        // Location & City
        $location = $this->location ?? null;
        $city = null;
        if ($location) {
            $locationParts = explode(',', $location);
            $city = trim($locationParts[0] ?? '');
        }

        // Dynamic Geographic Distance calculation
        $coords = $this->getCoordinates();
        $distanceKm = null;
        if ($userLat !== null && $userLng !== null && $coords['lat'] && $coords['lng']) {
            $distanceKm = self::calculateDistance($userLat, $userLng, $coords['lat'], $coords['lng']);
        } elseif (!empty($userCity) && !empty($location)) {
            $userCityLower = strtolower($userCity);
            if (str_contains(strtolower($location), $userCityLower)) {
                $distanceKm = 1.2;
            } else {
                $distanceKm = 15.0;
            }
        }

        // Tuition / Frais
        $fraisRaw = $this->averageAnnualTuitionFee();
        $frais = $fraisRaw !== null ? (int) $fraisRaw : null;

        // Score IA & Success rate — computed from the school's own validated
        // exam sessions and class promotions, never self-typed or fabricated.
        $scoreIA = null;
        $examSuccessRates = $this->examSuccessRates();
        // Legacy single-value fallback for older clients: whichever exam
        // type is highest-level and actually has a validated rate.
        $successRate = null;
        foreach (['bts', 'bac', 'bepc', 'cepe'] as $type) {
            if ($examSuccessRates[$type] !== null) {
                $successRate = $examSuccessRates[$type];
                break;
            }
        }
        $progressionAnnuelle = $this->computedProgressionAnnuelle();

        // Academic radar
        $radar = is_array($this->academic_radar) && !empty($this->academic_radar)
            ? array_map(fn($v) => (float)$v, $this->academic_radar)
            : null;

        // Nearby Places — real establishments/amenities around the school's
        // real GPS position, fetched from OpenStreetMap (see
        // NearbyAmenitiesService), not self-reported.
        $nearbyPlaces = (new \App\Modules\SuperAdmin\Application\Services\NearbyAmenitiesService())->forSchool($this);

        return [
            'id'                  => (string) $this->id,
            'name'                => $this->name,
            'location'            => $location,
            'city'                => $city,
            'distanceKm'          => $distanceKm,
            'latitude'            => $coords['lat'],
            'longitude'           => $coords['lng'],
            'scoreIA'             => $scoreIA,
            'successRate'         => $successRate,
            'successRates'        => $examSuccessRates,
            'availableExamTypes'  => $this->availableExamTypes(),
            'fraisAnnuels'        => $frais,
            'tags'                => $tags,
            'facilities'          => $facilitiesMap,
            'allFacilities'       => $facilitiesDetails,
            'imageUrl'            => $imageUrl,
            'galleryUrls'         => $galleryUrls,
            'isRecommended'       => false,
            'types'               => $types,
            'levels'              => $levels,
            'level'               => $level,
            'progressionAnnuelle' => $progressionAnnuelle,
            'ratioProf'           => $this->teacherStudentRatioLabel(),
            'academicRadar'       => $radar,
            'nearbyPlaces'        => $nearbyPlaces,
            'aiInsight'           => $this->slogan ?: ($this->description ?: null),
        ];
    }
}

