<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\Semester\CreateSemesterUseCase;
use App\Modules\Academic\Application\DTOs\CreateSemesterDTO;
use App\Modules\Academic\Domain\Repositories\SemesterRepositoryInterface;

class SemesterController extends Controller
{
    private $repository;
    
    public function __construct(SemesterRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateSemesterUseCase $useCase) {
        $dto = new CreateSemesterDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
