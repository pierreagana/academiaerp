<?php

namespace App\Modules\SuperAdmin\Domain\Entities;

use ArrayAccess;

class StaffMember implements ArrayAccess
{
    public function __construct(
        public readonly ?int $id = null,
        public readonly ?string $staffCode = null,
        public readonly ?string $name = null,
        public readonly ?string $email = null,
        public readonly ?string $role = null,
        public readonly ?string $department = null,
        public readonly ?string $status = null,
        public readonly ?string $lastLogin = null
    ) {}

    public function __get(string $name)
    {
        return match ($name) {
            'staff_code'  => $this->staffCode,
            'last_login'  => $this->lastLogin ?? 'Récemment',
            default       => property_exists($this, $name) ? $this->$name : null,
        };
    }

    public function offsetExists(mixed $offset): bool
    {
        return in_array($offset, ['id', 'staff_code', 'staffCode', 'name', 'email', 'role', 'department', 'status', 'last_login', 'lastLogin']);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return match ($offset) {
            'id'          => $this->id,
            'staff_code', 'staffCode' => $this->staffCode,
            'name'        => $this->name,
            'email'       => $this->email,
            'role'        => $this->role ?? 'Super Admin',
            'department'  => $this->department ?? 'Direction',
            'status'      => $this->status ?? 'Active',
            'last_login', 'lastLogin' => $this->lastLogin ?? 'Récemment',
            default       => null,
        };
    }

    public function offsetSet(mixed $offset, mixed $value): void {}
    public function offsetUnset(mixed $offset): void {}
}
