<?php

namespace App\Modules\Library\Infrastructure\Repositories;

use App\Modules\Library\Domain\Models\Book;
use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;

class EloquentBookRepository implements BookRepositoryInterface
{
    public function all()
    {
        return Book::where('school_id', auth()->user()->school_id)->with('category')->orderBy('title')->get();
    }

    public function paginate($perPage = 10, array $filters = [])
    {
        $query = Book::where('school_id', auth()->user()->school_id)->with('category')->latest();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('author', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'checked_out') {
                $query->where('quantity_available', '<=', 0);
            } elseif ($filters['status'] === 'low_stock') {
                $query->where('quantity_available', '>', 0)
                    ->whereRaw('quantity_available <= quantity_total * 0.25');
            } elseif ($filters['status'] === 'available') {
                $query->where('quantity_available', '>', 0)
                    ->whereRaw('quantity_available > quantity_total * 0.25');
            }
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function find($id)
    {
        return Book::where('school_id', auth()->user()->school_id)->with('category')->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        $data['quantity_available'] = $data['quantity_total'];
        return Book::create($data);
    }

    public function update($id, array $data)
    {
        $book = $this->find($id);

        if (isset($data['quantity_total'])) {
            $currentlyLoaned = $book->quantity_total - $book->quantity_available;
            $data['quantity_available'] = max(0, $data['quantity_total'] - $currentlyLoaned);
        }

        $book->update($data);
        return $book;
    }

    public function delete($id)
    {
        $book = $this->find($id);
        return $book->delete();
    }

    public function recent($limit = 5)
    {
        return Book::where('school_id', auth()->user()->school_id)
            ->with('category')
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function categoryCounts()
    {
        return Book::where('school_id', auth()->user()->school_id)
            ->selectRaw('category_id, sum(quantity_total) as total_items')
            ->groupBy('category_id')
            ->pluck('total_items', 'category_id');
    }
}
