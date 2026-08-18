<?php

namespace App\Modules\Library\Infrastructure\Repositories;

use App\Modules\Library\Domain\Models\Book;
use App\Modules\Library\Domain\Models\Loan;
use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use Illuminate\Support\Carbon;

class EloquentLoanRepository implements LoanRepositoryInterface
{
    public function paginateActive($perPage = 10, array $filters = [])
    {
        $query = Loan::where('school_id', auth()->user()->school_id)
            ->whereNull('returned_at')
            ->with(['book', 'borrower'])
            ->orderBy('due_at');

        return $query->paginate($perPage)->withQueryString();
    }

    public function find($id)
    {
        return Loan::where('school_id', auth()->user()->school_id)
            ->with(['book', 'borrower'])
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        $data['school_id'] = $data['school_id'] ?? auth()->user()->school_id;
        return Loan::create($data);
    }

    public function markReturned($id)
    {
        $loan = $this->find($id);
        $loan->update(['returned_at' => Carbon::today()]);
        $loan->book->increment('quantity_available');
        return $loan;
    }

    public function findActiveByBookIdentifier(string $identifier)
    {
        $book = Book::where('school_id', auth()->user()->school_id)
            ->where(function ($q) use ($identifier) {
                $q->where('isbn', $identifier)->orWhere('title', 'like', "%{$identifier}%");
            })
            ->first();

        if (!$book) {
            return null;
        }

        return Loan::where('school_id', auth()->user()->school_id)
            ->where('book_id', $book->id)
            ->whereNull('returned_at')
            ->orderBy('due_at')
            ->first();
    }

    public function overdue($limit = null)
    {
        $query = Loan::where('school_id', auth()->user()->school_id)
            ->whereNull('returned_at')
            ->where('due_at', '<', Carbon::today())
            ->with(['book', 'borrower'])
            ->orderBy('due_at');

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    public function distinctBorrowersCount(): int
    {
        return Loan::where('school_id', auth()->user()->school_id)
            ->select('borrower_type', 'borrower_id')
            ->distinct()
            ->get()
            ->count();
    }

    public function monthlyCounts(int $months): array
    {
        $schoolId = auth()->user()->school_id;
        $counts = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = Carbon::now()->subMonthsNoOverflow($i);
            $count = Loan::where('school_id', $schoolId)
                ->whereYear('borrowed_at', $month->year)
                ->whereMonth('borrowed_at', $month->month)
                ->count();

            $counts[] = [
                'label' => $month->translatedFormat('M'),
                'count' => $count,
            ];
        }

        return $counts;
    }

    public function markReminded(array $loanIds): void
    {
        Loan::where('school_id', auth()->user()->school_id)
            ->whereIn('id', $loanIds)
            ->update(['reminded_at' => now()]);
    }
}
