<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Library\Application\DTOs\CreateBookDTO;
use App\Modules\Library\Application\DTOs\CreateLoanDTO;
use App\Modules\Library\Application\DTOs\UpdateBookDTO;
use App\Modules\Library\Application\Services\LibraryStatsService;
use App\Modules\Library\Application\UseCases\CreateBookUseCase;
use App\Modules\Library\Application\UseCases\CreateLoanUseCase;
use App\Modules\Library\Application\UseCases\DeleteBookUseCase;
use App\Modules\Library\Application\UseCases\QuickReturnUseCase;
use App\Modules\Library\Application\UseCases\RemindOverdueLoansUseCase;
use App\Modules\Library\Application\UseCases\ReturnLoanUseCase;
use App\Modules\Library\Application\UseCases\UpdateBookUseCase;
use App\Modules\Library\Domain\Models\Book;
use App\Modules\Library\Domain\Repositories\BookCategoryRepositoryInterface;
use App\Modules\Library\Domain\Repositories\BookRepositoryInterface;
use App\Modules\Library\Domain\Repositories\LibrarySettingRepositoryInterface;
use App\Modules\Library\Domain\Repositories\LoanRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use RuntimeException;

class LibraryController extends Controller
{
    public function dashboard(LibraryStatsService $statsService, BookRepositoryInterface $bookRepository, LoanRepositoryInterface $loanRepository)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->dashboardStats($schoolId);
        $trend = $statsService->borrowingTrend();
        $overdueAlerts = $loanRepository->overdue(5);
        $newArrivals = $bookRepository->recent(5);

        return view('SchoolDashboard::library.dashboard', compact('stats', 'trend', 'overdueAlerts', 'newArrivals'));
    }

    public function catalog(Request $request, BookRepositoryInterface $bookRepository, BookCategoryRepositoryInterface $categoryRepository)
    {
        $filters = [
            'search' => $request->get('search'),
            'category_id' => $request->get('category_id'),
            'status' => $request->get('status'),
        ];

        $books = $bookRepository->paginate(10, $filters);
        $categories = $categoryRepository->all();
        $view = $request->get('view', 'list');

        // Real most-borrowed title (30 days) + real out-of-stock count —
        // replaces a generic "la demande peut fluctuer..." line with nothing
        // behind it.
        $schoolId = auth()->user()->school_id;
        $topBookId = \App\Modules\Library\Domain\Models\Loan::where('school_id', $schoolId)
            ->where('borrowed_at', '>=', now()->subDays(30))
            ->select('book_id', \Illuminate\Support\Facades\DB::raw('count(*) as c'))
            ->groupBy('book_id')
            ->orderByDesc('c')
            ->first();
        $topBook = $topBookId ? \App\Modules\Library\Domain\Models\Book::find($topBookId->book_id) : null;
        $outOfStockCount = \App\Modules\Library\Domain\Models\Book::where('school_id', $schoolId)
            ->where('quantity_available', '<=', 0)
            ->count();

        return view('SchoolDashboard::library.catalog', compact(
            'books', 'categories', 'filters', 'view', 'topBook', 'outOfStockCount'
        ));
    }

    public function storeBook(Request $request, CreateBookUseCase $useCase)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50', 'unique:books,isbn'],
            'category_id' => ['nullable', 'exists:book_categories,id'],
            'quantity_total' => ['required', 'integer', 'min:1'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('library/covers', 'public');
        }

        $useCase->execute(new CreateBookDTO($data));

        return back()->with('success', 'Livre ajouté au catalogue avec succès.');
    }

    public function updateBook(Request $request, $id, UpdateBookUseCase $useCase)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'author' => ['nullable', 'string', 'max:255'],
            'isbn' => ['nullable', 'string', 'max:50', 'unique:books,isbn,' . $id],
            'category_id' => ['nullable', 'exists:book_categories,id'],
            'quantity_total' => ['required', 'integer', 'min:0'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);

        if ($request->hasFile('cover')) {
            $data['cover_path'] = $request->file('cover')->store('library/covers', 'public');
        }

        try {
            $useCase->execute($id, new UpdateBookDTO($data));
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['quantity_total' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Livre mis à jour avec succès.');
    }

    public function destroyBook($id, DeleteBookUseCase $useCase)
    {
        $useCase->execute($id);
        return back()->with('success', 'Livre supprimé avec succès.');
    }

    public function circulation(LoanRepositoryInterface $loanRepository, LibrarySettingRepositoryInterface $settingRepository)
    {
        $loans = $loanRepository->paginateActive(10);
        $settings = $settingRepository->getForSchool(auth()->user()->school_id);
        return view('SchoolDashboard::library.circulation', compact('loans', 'settings'));
    }

    public function storeLoan(Request $request, CreateLoanUseCase $useCase)
    {
        $data = $request->validate([
            'borrower_type' => ['required', 'string', 'in:student,teacher,staff'],
            'borrower_identifier' => ['required', 'string', 'max:255'],
            'book_identifier' => ['required', 'string', 'max:255'],
        ]);

        $schoolId = auth()->user()->school_id;

        $borrower = $this->resolveBorrower($schoolId, $data['borrower_type'], $data['borrower_identifier']);
        if (!$borrower) {
            return back()->withErrors(['borrower_identifier' => 'Aucun emprunteur trouvé pour cet identifiant ou ce nom.'])->withInput();
        }

        $book = Book::where('school_id', $schoolId)
            ->where(function ($q) use ($data) {
                $q->where('isbn', $data['book_identifier'])->orWhere('title', 'like', '%' . $data['book_identifier'] . '%');
            })
            ->first();

        if (!$book) {
            return back()->withErrors(['book_identifier' => 'Aucun livre trouvé pour ce titre ou cet ISBN.'])->withInput();
        }

        try {
            $useCase->execute(new CreateLoanDTO([
                'book_id' => $book->id,
                'borrower_type' => $data['borrower_type'],
                'borrower_id' => $borrower->id,
            ]));
        } catch (RuntimeException $e) {
            return back()->withErrors(['book_identifier' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Emprunt enregistré avec succès.');
    }

    public function returnLoan($id, ReturnLoanUseCase $useCase)
    {
        $useCase->execute($id);
        return back()->with('success', 'Livre retourné avec succès.');
    }

    public function quickReturn(Request $request, QuickReturnUseCase $useCase)
    {
        $data = $request->validate([
            'book_identifier' => ['required', 'string', 'max:255'],
        ]);

        try {
            $useCase->execute($data['book_identifier']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['book_identifier' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Livre retourné avec succès.');
    }

    public function remindOverdue(RemindOverdueLoansUseCase $useCase)
    {
        $count = $useCase->execute();

        $message = $count > 0
            ? "{$count} rappel(s) enregistré(s) pour les emprunts en retard."
            : 'Aucun emprunt en retard à signaler.';

        return back()->with('success', $message);
    }

    public function remindLoan($id, LoanRepositoryInterface $loanRepository)
    {
        $loanRepository->find($id);
        $loanRepository->markReminded([$id]);

        return back()->with('success', 'Rappel enregistré pour cet emprunt.');
    }

    public function settings(LibrarySettingRepositoryInterface $settingRepository, BookCategoryRepositoryInterface $categoryRepository, LibraryStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;

        $settings = $settingRepository->getForSchool($schoolId);
        $categories = $categoryRepository->all();
        $systemStatus = $statsService->dashboardStats($schoolId);

        return view('SchoolDashboard::library.settings', compact('settings', 'categories', 'systemStatus'));
    }

    public function updateRules(Request $request, LibrarySettingRepositoryInterface $settingRepository)
    {
        $data = $request->validate([
            'max_books_per_student' => ['required', 'integer', 'min:1'],
            'loan_duration_days' => ['required', 'integer', 'min:1'],
            'late_fee_per_day' => ['required', 'numeric', 'min:0'],
        ]);

        $data['enforce_fees'] = $request->boolean('enforce_fees');

        $settingRepository->updateRules(auth()->user()->school_id, $data);

        return back()->with('success', 'Règles de circulation mises à jour.');
    }

    public function storeCategory(Request $request, BookCategoryRepositoryInterface $categoryRepository)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('book_categories')->where('school_id', $schoolId)],
            'color' => ['nullable', 'string', 'max:20'],
        ]);

        $categoryRepository->create($data);

        return back()->with('success', 'Catégorie ajoutée avec succès.');
    }

    public function destroyCategory($id, BookCategoryRepositoryInterface $categoryRepository)
    {
        $categoryRepository->delete($id);
        return back()->with('success', 'Catégorie supprimée avec succès.');
    }

    public function updateAccessSettings(Request $request, LibrarySettingRepositoryInterface $settingRepository)
    {
        $data = $request->validate([
            'card_format' => ['required', 'string', 'in:digital_qr,physical_barcode'],
        ]);

        $data['auto_renew_staff_cards'] = $request->boolean('auto_renew_staff_cards');

        $settingRepository->updateAccess(auth()->user()->school_id, $data);

        return back()->with('success', "Paramètres d'accès mis à jour.");
    }

    private function resolveBorrower(int $schoolId, string $type, string $identifier)
    {
        $like = '%' . $identifier . '%';

        if ($type === 'student') {
            return Student::where('school_id', $schoolId)
                ->where(function ($q) use ($identifier, $like) {
                    $q->where('roll_number', $identifier)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                })
                ->first();
        }

        if ($type === 'teacher') {
            return Teacher::where('school_id', $schoolId)
                ->where(function ($q) use ($identifier, $like) {
                    $q->where('employee_id', $identifier)
                        ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
                })
                ->first();
        }

        return Staff::where('school_id', $schoolId)
            ->where(function ($q) use ($identifier, $like) {
                $q->where('employee_id', $identifier)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", [$like]);
            })
            ->first();
    }
}
