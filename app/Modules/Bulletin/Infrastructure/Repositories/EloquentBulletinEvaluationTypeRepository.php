<?php

namespace App\Modules\Bulletin\Infrastructure\Repositories;

use App\Modules\Bulletin\Domain\Models\BulletinEvaluationType;
use App\Modules\Bulletin\Domain\Repositories\BulletinEvaluationTypeRepositoryInterface;

class EloquentBulletinEvaluationTypeRepository implements BulletinEvaluationTypeRepositoryInterface
{
    public function all()
    {
        return BulletinEvaluationType::where('school_id', auth()->user()->school_id)->orderByDesc('coefficient')->orderBy('name')->get();
    }

    public function find($id)
    {
        return BulletinEvaluationType::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return BulletinEvaluationType::create($data);
    }

    public function update($id, array $data)
    {
        $type = $this->find($id);
        $type->update($data);
        return $type;
    }

    public function delete($id)
    {
        $type = $this->find($id);
        return $type->delete();
    }
}
