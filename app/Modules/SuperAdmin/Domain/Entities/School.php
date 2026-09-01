<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class School
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $name = null,
        public readonly ?string $code = null,
        public readonly ?string $logo = null,
        public readonly ?string $location = null,
        public readonly ?string $region = null,
        public readonly ?string $status = null,
        public readonly int $studentsCount = 0,
        public readonly ?string $package = null,
        public readonly ?string $type = null,
        public readonly ?string $contactEmail = null,
        public readonly ?string $contactPhone = null,
        public readonly ?string $domain = null,
        public readonly ?string $renewalDate = null,
        public readonly ?float $latitude = null,
        public readonly ?float $longitude = null,
        public readonly ?string $sector = null,
        public readonly bool $isBilingual = false,
        public readonly ?string $languageRegime = null,
        public readonly ?array $levels = null
    ) {}

    public function __get(string $name): mixed
    {
        return match ($name) {
            'students_count'  => $this->studentsCount,
            'plan_name'       => $this->package,
            'contact_email'   => $this->contactEmail,
            'contact_phone'   => $this->contactPhone,
            'is_bilingual'    => $this->isBilingual,
            'language_regime' => $this->languageRegime,
            default           => property_exists($this, $name) ? $this->{$name} : null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'students_count', 'plan_name', 'contact_email', 'contact_phone', 'is_bilingual', 'language_regime' => true,
            default => property_exists($this, $name),
        };
    }
}
