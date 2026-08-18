<?php

namespace App\Modules\Cards\Infrastructure\Repositories;

use App\Modules\Cards\Domain\Models\CardTemplate;
use App\Modules\Cards\Domain\Repositories\CardTemplateRepositoryInterface;

class EloquentCardTemplateRepository implements CardTemplateRepositoryInterface
{
    public function findOrDefault(string $type)
    {
        $schoolId = auth()->user()->school_id;

        $template = CardTemplate::where('school_id', $schoolId)->where('card_type', $type)->first();

        if (!$template) {
            $template = CardTemplate::create(array_merge(
                CardTemplate::defaultsFor($type),
                ['school_id' => $schoolId, 'card_type' => $type]
            ));
        }

        return $template;
    }

    public function save(string $type, array $data)
    {
        $template = $this->findOrDefault($type);
        $template->update($data);

        return $template;
    }
}
