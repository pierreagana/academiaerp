<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Finance\Application\DTOs\CreateExpenseBudgetDTO;
use App\Modules\Finance\Application\DTOs\CreateExpenseDTO;
use App\Modules\Finance\Application\DTOs\UpdateExpenseDTO;
use App\Modules\Finance\Application\Services\ExpenseStatsService;
use App\Modules\Finance\Application\UseCases\CreateExpenseBudgetUseCase;
use App\Modules\Finance\Application\UseCases\CreateExpenseUseCase;
use App\Modules\Finance\Application\UseCases\DeleteExpenseBudgetUseCase;
use App\Modules\Finance\Application\UseCases\DeleteExpenseUseCase;
use App\Modules\Finance\Application\UseCases\GenerateSalaryExpensesUseCase;
use App\Modules\Finance\Application\UseCases\UpdateExpenseUseCase;
use App\Modules\Finance\Domain\Models\Expense;
use App\Modules\Finance\Domain\Repositories\ExpenseBudgetRepositoryInterface;
use App\Modules\Finance\Domain\Repositories\ExpenseRepositoryInterface;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function overview(ExpenseStatsService $statsService)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->monthlyStats($schoolId);
        $breakdown = $statsService->categoryBreakdown($schoolId);
        $recent = $statsService->recentExpenses($schoolId, 5);

        return view('SchoolDashboard::finance.expenses.overview', compact('stats', 'breakdown', 'recent'));
    }

    public function transactions(Request $request)
    {
        $schoolId = auth()->user()->school_id;

        $period = $request->get('period', 'this_month');
        $category = $request->get('category');
        $status = $request->get('status');

        $query = Expense::where('school_id', $schoolId)->latest('expense_date')->latest('id');

        if ($period === 'this_month') {
            $query->where('expense_date', '>=', now()->startOfMonth());
        } elseif ($period === 'last_month') {
            $query->whereBetween('expense_date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()]);
        } elseif ($period === 'this_year') {
            $query->where('expense_date', '>=', now()->startOfYear());
        }

        if (!empty($category)) {
            $query->where('category', $category);
        }

        if (!empty($status)) {
            $query->where('status', $status);
        }

        $transactions = $query->paginate(10)->withQueryString();

        $categories = Expense::where('school_id', $schoolId)->distinct()->pluck('category');

        return view('SchoolDashboard::finance.expenses.transactions', compact('transactions', 'categories', 'period', 'category', 'status'));
    }

    public function create()
    {
        $categories = array_keys(Expense::CATEGORY_ICONS);
        return view('SchoolDashboard::finance.expenses.create', compact('categories'));
    }

    public function store(Request $request, CreateExpenseUseCase $useCase)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'category' => ['required', 'string', 'max:255'],
            'payee' => ['nullable', 'string', 'max:255'],
            'proof' => ['required', 'file', 'max:5120'],
            'note' => ['nullable', 'string'],
        ]);

        $data['proof_path'] = $request->file('proof')->store('expenses/proofs', 'public');
        unset($data['proof']);
        $data['status'] = 'pending';
        $data['recorded_by'] = auth()->id();

        $useCase->execute(new CreateExpenseDTO($data));

        return redirect()->route('school.finance.expenses.transactions')->with('success', 'Dépense enregistrée et mise en attente de validation.');
    }

    public function approve($id, UpdateExpenseUseCase $useCase)
    {
        $useCase->execute($id, new UpdateExpenseDTO(['status' => 'approved']));
        return back()->with('success', 'Dépense validée avec succès.');
    }

    public function reject($id, UpdateExpenseUseCase $useCase)
    {
        $useCase->execute($id, new UpdateExpenseDTO(['status' => 'rejected']));
        return back()->with('success', 'Dépense rejetée.');
    }

    public function destroy($id, DeleteExpenseUseCase $useCase)
    {
        $useCase->execute($id);
        return back()->with('success', 'Dépense supprimée avec succès.');
    }

    public function generateSalaries(GenerateSalaryExpensesUseCase $useCase)
    {
        $count = $useCase->execute(auth()->user()->school_id, auth()->id());

        $message = $count > 0
            ? "$count dépense(s) salariale(s) générée(s) pour ce mois."
            : 'Tous les salaires de ce mois ont déjà été générés.';

        return back()->with('success', $message);
    }

    public function budgets(ExpenseBudgetRepositoryInterface $repository)
    {
        $budgets = $repository->all();
        $categories = array_keys(Expense::CATEGORY_ICONS);
        $currentYear = now()->month >= 8 ? now()->year . '-' . (now()->year + 1) : (now()->year - 1) . '-' . now()->year;

        return view('SchoolDashboard::finance.expenses.budgets', compact('budgets', 'categories', 'currentYear'));
    }

    public function storeBudget(Request $request, CreateExpenseBudgetUseCase $useCase)
    {
        $data = $request->validate([
            'category' => ['required', 'string', 'max:255'],
            'period' => ['required', 'string', 'in:monthly,quarterly,annual'],
            'academic_year' => ['required', 'string', 'max:20'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $useCase->execute(new CreateExpenseBudgetDTO($data));

        return redirect()->route('school.finance.expenses.budgets')->with('success', 'Budget enregistré avec succès.');
    }

    public function destroyBudget($id, DeleteExpenseBudgetUseCase $useCase)
    {
        $useCase->execute($id);
        return redirect()->route('school.finance.expenses.budgets')->with('success', 'Budget supprimé avec succès.');
    }

    public function exportExcel()
    {
        $schoolId = auth()->user()->school_id;
        $expenses = Expense::where('school_id', $schoolId)->latest('expense_date')->get();

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=depenses_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Référence', 'Libellé', 'Catégorie', 'Bénéficiaire', 'Montant', 'Statut'], ',', '"', '\\');

            foreach ($expenses as $e) {
                fputcsv($file, [
                    $e->expense_date->format('d/m/Y'),
                    $e->reference,
                    $e->title,
                    $e->category,
                    $e->payee ?? '-',
                    $e->amount,
                    Expense::STATUSES[$e->status] ?? $e->status,
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'depenses_' . date('Y-m-d') . '.csv', $headers);
    }

    public function exportPdf()
    {
        $schoolId = auth()->user()->school_id;
        $expenses = Expense::where('school_id', $schoolId)->latest('expense_date')->get();
        $school = auth()->user()->school;

        return view('SchoolDashboard::finance.expenses.print', compact('expenses', 'school'));
    }
}
