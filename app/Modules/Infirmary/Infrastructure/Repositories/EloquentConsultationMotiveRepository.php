<?php

namespace App\Modules\Infirmary\Infrastructure\Repositories;

use App\Modules\Infirmary\Domain\Models\ConsultationMotive;
use App\Modules\Infirmary\Domain\Repositories\ConsultationMotiveRepositoryInterface;

class EloquentConsultationMotiveRepository implements ConsultationMotiveRepositoryInterface
{
    private const DEFAULTS = ['Céphalée', 'Fièvre', 'Mal de Ventre', 'Blessure', 'Nausée', 'Autre'];

    public function all()
    {
        return ConsultationMotive::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function create(string $name)
    {
        return ConsultationMotive::firstOrCreate([
            'school_id' => auth()->user()->school_id,
            'name' => $name,
        ]);
    }

    public function delete($id)
    {
        $motive = ConsultationMotive::where('school_id', auth()->user()->school_id)->findOrFail($id);
        return $motive->delete();
    }

    public function ensureDefaults(): void
    {
        if (ConsultationMotive::where('school_id', auth()->user()->school_id)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $name) {
            $this->create($name);
        }
    }
}
