<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

class RegistrationRequest
{
    public function __construct(
        public int|string $id,
        public string $schoolName,
        public string $applicantName,
        public string $email,
        public string $phone,
        public string $region,
        public string $status,
        public string $submittedAt,
        public string $packageRequested,
        public ?string $requestCode = null
    ) {}

    public function __get(string $name): mixed
    {
        return match ($name) {
            'school_name'      => $this->schoolName,
            'applicant_name'   => $this->applicantName,
            'plan_requested'   => $this->packageRequested,
            'created_at'       => $this->submittedAt,
            'request_code'     => $this->requestCode ?? '#REQ-' . str_pad((string)$this->id, 4, '0', STR_PAD_LEFT),
            default            => property_exists($this, $name) ? $this->{$name} : null,
        };
    }

    public function __isset(string $name): bool
    {
        return match ($name) {
            'school_name', 'applicant_name', 'plan_requested', 'created_at', 'request_code' => true,
            default => property_exists($this, $name),
        };
    }
}
