<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\Subject\CreateSubjectUseCase;
use App\Modules\Academic\Application\DTOs\CreateSubjectDTO;
use App\Modules\Academic\Domain\Repositories\SubjectRepositoryInterface;

class SubjectController extends Controller
{
    private $repository;
    
    public function __construct(SubjectRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateSubjectUseCase $useCase) {
        $dto = new CreateSubjectDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
