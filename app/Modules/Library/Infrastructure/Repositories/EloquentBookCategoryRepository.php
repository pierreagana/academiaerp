<?php

namespace App\Modules\Library\Infrastructure\Repositories;

use App\Modules\Library\Domain\Models\BookCategory;
use App\Modules\Library\Domain\Repositories\BookCategoryRepositoryInterface;

class EloquentBookCategoryRepository implements BookCategoryRepositoryInterface
{
    public function all()
    {
        return BookCategory::where('school_id', auth()->user()->school_id)
            ->withCount('books')
            ->orderBy('name')
            ->get();
    }

    public function find($id)
    {
        return BookCategory::where('school_id', auth()->user()->school_id)->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return BookCategory::create($data);
    }

    public function delete($id)
    {
        $category = $this->find($id);
        return $category->delete();
    }
}
