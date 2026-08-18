<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\AcademicClass\CreateAcademicClassUseCase;
use App\Modules\Academic\Application\DTOs\CreateAcademicClassDTO;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;

class AcademicClassController extends Controller
{
    private $repository;
    
    public function __construct(AcademicClassRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateAcademicClassUseCase $useCase) {
        $dto = new CreateAcademicClassDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
