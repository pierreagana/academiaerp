<?php

namespace App\Modules\Library\Application\Services;

use App\Modules\Library\Domain\Models\Book;
use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;

class LibraryStatsService
{
    private LoanRepositoryInterface $loanRepository;

    public function __construct(LoanRepositoryInterface $loanRepository)
    {
        $this->loanRepository = $loanRepository;
    }

    public function dashboardStats(int $schoolId): array
    {
        $totalBooks = (int) Book::where('school_id', $schoolId)->sum('quantity_total');
        $available = (int) Book::where('school_id', $schoolId)->sum('quantity_available');
        $booksOut = max(0, $totalBooks - $available);

        return [
            'total_books' => $totalBooks,
            'books_out' => $booksOut,
            'books_out_percent' => $totalBooks > 0 ? round(($booksOut / $totalBooks) * 100) : 0,
            'overdue_returns' => $this->loanRepository->overdue()->count(),
            'active_members' => $this->loanRepository->distinctBorrowersCount(),
        ];
    }

    public function borrowingTrend(): array
    {
        return $this->loanRepository->monthlyCounts(6);
    }
}
