<?php

namespace App\Modules\Transport\Infrastructure\Repositories;

use App\Modules\Transport\Domain\Models\Route;
use App\Modules\Transport\Domain\Repositories\RouteRepositoryInterface;

class EloquentRouteRepository implements RouteRepositoryInterface
{
    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Route::create($data);
    }

    public function update($id, array $data)
    {
        $route = $this->find($id);
        $route->update($data);
        return $route;
    }

    public function find($id)
    {
        return Route::where('school_id', auth()->user()->school_id)->with(['bus.driver', 'stops'])->findOrFail($id);
    }

    public function all()
    {
        return Route::where('school_id', auth()->user()->school_id)
            ->with(['bus.driver', 'stops'])
            ->withCount('stops')
            ->orderBy('name')
            ->get();
    }

    public function active()
    {
        return Route::where('school_id', auth()->user()->school_id)
            ->where('status', 'actif')
            ->with('stops')
            ->orderBy('name')
            ->get();
    }
}
