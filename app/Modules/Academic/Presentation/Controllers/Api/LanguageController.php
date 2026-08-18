<?php

namespace App\Modules\Academic\Presentation\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Modules\Academic\Application\UseCases\Language\CreateLanguageUseCase;
use App\Modules\Academic\Application\DTOs\CreateLanguageDTO;
use App\Modules\Academic\Domain\Repositories\LanguageRepositoryInterface;

class LanguageController extends Controller
{
    private $repository;
    
    public function __construct(LanguageRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function index() { return response()->json($this->repository->all()); }
    
    public function store(Request $request, CreateLanguageUseCase $useCase) {
        $dto = new CreateLanguageDTO($request->all());
        $result = $useCase->execute($dto);
        return response()->json($result, 201);
    }
}
