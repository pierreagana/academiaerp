<?php

namespace App\Modules\Canteen\Infrastructure\Repositories;

use App\Modules\Canteen\Domain\Models\Allergen;
use App\Modules\Canteen\Domain\Repositories\AllergenRepositoryInterface;

class EloquentAllergenRepository implements AllergenRepositoryInterface
{
    private const DEFAULTS = ['Gluten', 'Lait', 'Œufs', 'Arachides', 'Poisson', 'Soja'];

    public function all()
    {
        return Allergen::where('school_id', auth()->user()->school_id)->orderBy('name')->get();
    }

    public function create(string $name)
    {
        return Allergen::firstOrCreate([
            'school_id' => auth()->user()->school_id,
            'name' => $name,
        ]);
    }

    public function delete($id)
    {
        $allergen = Allergen::where('school_id', auth()->user()->school_id)->findOrFail($id);
        return $allergen->delete();
    }

    public function ensureDefaults(): void
    {
        if (Allergen::where('school_id', auth()->user()->school_id)->exists()) {
            return;
        }

        foreach (self::DEFAULTS as $name) {
            $this->create($name);
        }
    }
}
