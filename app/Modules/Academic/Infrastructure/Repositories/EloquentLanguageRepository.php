<?php

namespace App\Modules\Academic\Infrastructure\Repositories;

use App\Modules\Academic\Domain\Models\Language;
use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;

class EloquentLanguageRepository implements LanguageRepositoryInterface
{
    public function all() { return Language::all(); }
    public function find($id) { return Language::findOrFail($id); }
    public function create(array $data) { return Language::create($data); }
    public function update($id, array $data) { 
        $model = $this->find($id);
        $model->update($data);
        return $model;
    }
    public function delete($id) { return $this->find($id)->delete(); }
}
