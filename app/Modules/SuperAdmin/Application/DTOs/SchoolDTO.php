<?php

namespace App\Modules\SuperAdmin\Application\DTOs;

class SchoolDTO
{
    public function __construct(
        public readonly ?int    $id,
        public readonly string  $name,
        public readonly string  $type,
        public readonly string  $status,
        public readonly string  $planName,
        public readonly int     $studentsCount,
        public readonly float   $storageUsedGb,
        public readonly ?string $location,
        public readonly ?string $contactEmail,
        public readonly ?string $contactPhone,
        public readonly ?string $foundedDate,
        public readonly ?string $domain,
        public readonly ?string $themeColor,
        public readonly ?string $logoUrl,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            id:             $data['id'] ?? null,
            name:           $data['name'],
            type:           $data['type'] ?? 'Établissement',
            status:         $data['status'] ?? 'actif',
            planName:       $data['plan_name'] ?? 'Starter',
            studentsCount:  $data['students_count'] ?? 0,
            storageUsedGb:  $data['storage_used_gb'] ?? 0.0,
            location:       $data['location'] ?? null,
            contactEmail:   $data['contact_email'] ?? null,
            contactPhone:   $data['contact_phone'] ?? null,
            foundedDate:    $data['founded_date'] ?? null,
            domain:         $data['domain'] ?? null,
            themeColor:     $data['theme_color'] ?? null,
            logoUrl:        $data['logo_url'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'type'             => $this->type,
            'status'           => $this->status,
            'plan_name'        => $this->planName,
            'students_count'   => $this->studentsCount,
            'storage_used_gb'  => $this->storageUsedGb,
            'location'         => $this->location,
            'contact_email'    => $this->contactEmail,
            'contact_phone'    => $this->contactPhone,
            'founded_date'     => $this->foundedDate,
            'domain'           => $this->domain,
            'theme_color'      => $this->themeColor,
            'logo_url'         => $this->logoUrl,
        ];
    }
}
