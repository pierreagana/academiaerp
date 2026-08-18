<?php

namespace App\Modules\Infirmary\Domain\Repositories;

interface ConsultationMotiveRepositoryInterface
{
    public function all();

    public function create(string $name);

    public function delete($id);

    public function ensureDefaults(): void;
}
