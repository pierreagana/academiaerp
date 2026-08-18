<?php

namespace App\Modules\Presence\Infrastructure\Repositories;

use App\Modules\Presence\Domain\Models\AccessPoint;
use App\Modules\Presence\Domain\Repositories\AccessPointRepositoryInterface;

class EloquentAccessPointRepository implements AccessPointRepositoryInterface
{
    private const DEFAULTS = ['Portail Principal', 'Entrée Staff'];

    public function all()
    {
        return AccessPoint::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function create(string $name)
    {
        return AccessPoint::firstOrCreate([
            'school_id' => auth()->user()->school_id,
            'name' => $name,
        ]);
    }

    public function delete($id)
    {
        return AccessPoint::where('school_id', auth()->user()->school_id)->findOrFail($id)->delete();
    }

    public function ensureDefaults(): void
    {
        if (AccessPoint::where('school_id', auth()->user()->school_id)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $name) {
            $this->create($name);
        }
    }
}
