<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\Timetable\CreateTimetableUseCase;
use App\Modules\Academic\Application\DTOs\CreateTimetableDTO;
use App\Modules\Academic\Domain\Repositories\TimetableRepositoryInterface;

class TimetableController extends Controller
{
    private $repository;
    
    public function __construct(TimetableRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateTimetableUseCase $useCase) {
        $dto = new CreateTimetableDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
