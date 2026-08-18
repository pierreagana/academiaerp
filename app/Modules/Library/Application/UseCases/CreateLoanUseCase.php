<?php

namespace App\Modules\Library\Application\UseCases;

use App\Modules\Library\Application\DTOs\CreateLoanDTO;
use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;
use App\Modules\Library\Domain\Repositories\LibrarySettingRepositoryInterface;
use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use RuntimeException;

class CreateLoanUseCase
{
    private LoanRepositoryInterface $loanRepository;
    private BookRepositoryInterface $bookRepository;
    private LibrarySettingRepositoryInterface $settingRepository;

    public function __construct(
        LoanRepositoryInterface $loanRepository,
        BookRepositoryInterface $bookRepository,
        LibrarySettingRepositoryInterface $settingRepository
    ) {
        $this->loanRepository = $loanRepository;
        $this->bookRepository = $bookRepository;
        $this->settingRepository = $settingRepository;
    }

    public function execute(CreateLoanDTO $dto)
    {
        $book = $this->bookRepository->find($dto->data['book_id']);

        if ($book->quantity_available <= 0) {
            throw new RuntimeException("Aucun exemplaire disponible pour ce livre.");
        }

        $settings = $this->settingRepository->getForSchool($book->school_id);

        $loan = $this->loanRepository->create([
            'school_id' => $book->school_id,
            'book_id' => $book->id,
            'borrower_type' => $dto->data['borrower_type'],
            'borrower_id' => $dto->data['borrower_id'],
            'borrowed_at' => now()->toDateString(),
            'due_at' => now()->addDays($settings->loan_duration_days)->toDateString(),
        ]);

        $book->decrement('quantity_available');

        return $loan;
    }
}
