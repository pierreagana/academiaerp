<?php

namespace App\Modules\Bulletin\Infrastructure\Repositories;

use App\Modules\Bulletin\Domain\Models\BulletinTemplate;
use App\Modules\Bulletin\Domain\Repositories\BulletinTemplateRepositoryInterface;

class EloquentBulletinTemplateRepository implements BulletinTemplateRepositoryInterface
{
    public function findOrDefault(int $schoolId)
    {
        return BulletinTemplate::firstOrCreate(
            ['school_id' => $schoolId],
            ['name' => 'Modèle Standard']
        );
    }

    public function save(int $schoolId, array $data)
    {
        $template = $this->findOrDefault($schoolId);
        $template->update($data);

        return $template;
    }
}
