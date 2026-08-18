<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\Syllabus\CreateSyllabusUseCase;
use App\Modules\Academic\Application\DTOs\CreateSyllabusDTO;
use App\Modules\Academic\Domain\Repositories\SyllabusRepositoryInterface;

class SyllabusController extends Controller
{
    private $repository;
    
    public function __construct(SyllabusRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateSyllabusUseCase $useCase) {
        $dto = new CreateSyllabusDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
