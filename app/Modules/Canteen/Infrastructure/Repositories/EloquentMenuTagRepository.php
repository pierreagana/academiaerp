<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\MenuTag;
use App\Modules\Canteen\Domain\Repositories\MenuTagRepositoryInterface;

class EloquentMenuTagRepository implements MenuTagRepositoryInterface
{
    public function all()
    {
        return MenuTag::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function create(string $name)
    {
        return MenuTag::firstOrCreate([
            'school_id' => auth()->user()->school_id,
            'name' => $name,
        ]);
    }

    public function delete($id)
    {
        $tag = MenuTag::where('school_id', auth()->user()->school_id)->findOrFail($id);
        return $tag->delete();
    }

    public function ensureDefaults(): void
    {
        if (MenuTag::where('school_id', auth()->user()->school_id)->exists()) {
            return;
        }

        $this->create('Végétarien');
    }
}
