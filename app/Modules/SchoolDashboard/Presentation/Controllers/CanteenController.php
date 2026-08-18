<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Canteen\Application\DTOs\AdjustStockDTO;
use App\Modules\Canteen\Application\DTOs\CreateMealRecordDTO;
use App\Modules\Canteen\Application\DTOs\CreateMenuItemDTO;
use App\Modules\Canteen\Application\DTOs\CreateProductDTO;
use App\Modules\Canteen\Application\Services\CanteenStatsService;
use App\Modules\Canteen\Application\UseCases\AdjustStockUseCase;
use App\Modules\Canteen\Application\UseCases\CreateProductUseCase;
use App\Modules\Canteen\Application\UseCases\CreditAccountUseCase;
use App\Modules\Canteen\Application\UseCases\DeleteMenuItemUseCase;
use App\Modules\Canteen\Application\UseCases\PublishWeekUseCase;
use App\Modules\Canteen\Application\UseCases\RecordMealUseCase;
use App\Modules\Canteen\Application\UseCases\SaveMenuItemUseCase;
use App\Modules\Canteen\Application\UseCases\SyncRosterUseCase;
use App\Modules\Canteen\Domain\Models\Account;
use App\Modules\Canteen\Domain\Models\CanteenReservation;
use App\Modules\Canteen\Domain\Models\MenuItem;
use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MenuTagRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\AllergenRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use RuntimeException;

class CanteenController extends Controller
{
    public function dashboard(CanteenStatsService $statsService)
    {
        $stats = $statsService->dashboardStats();
        $criticalProducts = $statsService->criticalProducts();

        return view('SchoolDashboard::canteen.dashboard', compact('stats', 'criticalProducts'));
    }

    public function planning(
        Request $request,
        MenuRepositoryInterface $menuRepository,
        ProductRepositoryInterface $productRepository,
        MenuTagRepositoryInterface $tagRepository,
        AllergenRepositoryInterface $allergenRepository
    ) {
        $week = $request->get('week', MenuItem::currentWeekStart()->format('Y-m-d'));
        $current = Carbon::parse($week);

        $tagRepository->ensureDefaults();
        $allergenRepository->ensureDefaults();

        $items = $menuRepository->itemsForWeek($week);
        $itemsByCell = $items->groupBy(fn ($item) => $item->date->format('Y-m-d') . '-' . $item->slot);
        $menuWeek = $menuRepository->weekFor($week);
        $criticalProducts = $productRepository->criticalOrLow();
        $tags = $tagRepository->all();
        $allergens = $allergenRepository->all();

        $prevWeek = $current->copy()->subWeek()->format('Y-m-d');
        $nextWeek = $current->copy()->addWeek()->format('Y-m-d');

        return view('SchoolDashboard::canteen.planning', compact(
            'current', 'itemsByCell', 'menuWeek', 'criticalProducts', 'prevWeek', 'nextWeek', 'week', 'tags', 'allergens'
        ));
    }

    public function storeTag(Request $request, MenuTagRepositoryInterface $tagRepository)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $tagRepository->create($data['name']);

        return back()->with('success', 'Étiquette ajoutée avec succès.');
    }

    public function destroyTag($id, MenuTagRepositoryInterface $tagRepository)
    {
        $tagRepository->delete($id);
        return back()->with('success', 'Étiquette supprimée avec succès.');
    }

    public function storeAllergen(Request $request, AllergenRepositoryInterface $allergenRepository)
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100']]);
        $allergenRepository->create($data['name']);

        return back()->with('success', 'Allergène ajouté avec succès.');
    }

    public function destroyAllergen($id, AllergenRepositoryInterface $allergenRepository)
    {
        $allergenRepository->delete($id);
        return back()->with('success', 'Allergène supprimé avec succès.');
    }

    public function storeMenuItem(Request $request, SaveMenuItemUseCase $useCase)
    {
        $data = $request->validate([
            'id' => ['nullable', 'exists:canteen_menu_items,id'],
            'date' => ['required', 'date'],
            'slot' => ['required', 'string', 'in:breakfast,starter,main,dessert'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:100'],
            'allergens' => ['nullable', 'array'],
            'allergens.*' => ['string', 'max:100'],
            'week' => ['required', 'date'],
        ]);

        $week = $data['week'];
        unset($data['week']);

        $useCase->execute(new CreateMenuItemDTO($data));

        return redirect()->route('school.canteen.planning', ['week' => $week])->with('success', 'Plat enregistré avec succès.');
    }

    public function destroyMenuItem(Request $request, $id, DeleteMenuItemUseCase $useCase)
    {
        $week = $request->get('week', MenuItem::currentWeekStart()->format('Y-m-d'));
        $useCase->execute($id);

        return redirect()->route('school.canteen.planning', ['week' => $week])->with('success', 'Plat supprimé avec succès.');
    }

    public function publishWeek(Request $request, PublishWeekUseCase $useCase)
    {
        $data = $request->validate(['week' => ['required', 'date']]);
        $useCase->execute($data['week']);

        return redirect()->route('school.canteen.planning', ['week' => $data['week']])->with('success', 'Menu publié avec succès.');
    }

    public function printRecipeCards(Request $request, MenuRepositoryInterface $menuRepository)
    {
        $week = $request->get('week', MenuItem::currentWeekStart()->format('Y-m-d'));
        $current = Carbon::parse($week);
        $items = $menuRepository->itemsForWeek($week)->sortBy('date');
        $school = auth()->user()->school;

        return view('SchoolDashboard::canteen.print', compact('current', 'items', 'school'));
    }

    public function inventory(ProductRepositoryInterface $productRepository)
    {
        $products = $productRepository->paginate(10);
        $recentMovements = $productRepository->recentMovements(5);

        return view('SchoolDashboard::canteen.inventory', compact('products', 'recentMovements'));
    }

    public function exportInventory(ProductRepositoryInterface $productRepository)
    {
        $products = $productRepository->all();
        $statusLabels = ['optimal' => 'Optimal', 'low_stock' => 'Stock Faible', 'critical' => 'Critique', 'expiring_soon' => 'Bientôt Périmé'];

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=stocks_cantine_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($products, $statusLabels) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Produit', 'Catégorie', 'Quantité', 'Unité', 'Statut', 'Expiration'], ',', '"', '\\');

            foreach ($products as $product) {
                fputcsv($file, [
                    $product->name,
                    $product->category ?? '-',
                    $product->quantity,
                    $product->unit,
                    $statusLabels[$product->status] ?? $product->status,
                    $product->expiry_date ? $product->expiry_date->format('d/m/Y') : '-',
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'stocks_cantine_' . date('Y-m-d') . '.csv', $headers);
    }

    public function storeProduct(Request $request, CreateProductUseCase $useCase)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'unit' => ['required', 'string', 'max:20'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'low_stock_threshold' => ['nullable', 'numeric', 'min:0'],
            'critical_threshold' => ['nullable', 'numeric', 'min:0'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $useCase->execute(new CreateProductDTO($data));

        return back()->with('success', 'Produit ajouté au stock avec succès.');
    }

    public function adjustStock(Request $request, AdjustStockUseCase $useCase)
    {
        $data = $request->validate([
            'product_id' => ['required', 'exists:canteen_products,id'],
            'type' => ['required', 'string', 'in:in,out'],
            'category' => ['nullable', 'string', 'in:usage,waste,expired,delivery,adjustment'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'source' => ['nullable', 'string', 'max:255'],
        ]);

        try {
            $useCase->execute(new AdjustStockDTO($data));
        } catch (RuntimeException $e) {
            return back()->withErrors(['quantity' => $e->getMessage()])->withInput();
        }

        return back()->with('success', 'Stock mis à jour avec succès.');
    }

    public function reservations(
        Request $request,
        SyncRosterUseCase $syncRosterUseCase,
        AccountRepositoryInterface $accountRepository,
        AcademicClassRepositoryInterface $classRepository,
        MealRecordRepositoryInterface $mealRepository
    ) {
        $syncRosterUseCase->execute();

        $filters = [
            'status' => $request->get('status'),
            'holder_type' => $request->get('holder_type'),
            'class_id' => $request->get('class_id'),
            'search' => $request->get('search'),
        ];

        $schoolId = auth()->user()->school_id;

        $accounts = $accountRepository->paginate(15, $filters);
        $accounts->getCollection()->each(function ($account) use ($accountRepository) {
            $account->meals_this_month = $accountRepository->mealsCountThisMonth($account->id);
        });
        $classes = $classRepository->all();

        $week = MenuItem::currentWeekStart()->format('Y-m-d');
        $dailyCounts = $mealRepository->dailyCountsForWeek($week);
        $mealsToday = $mealRepository->countToday();
        $negativeBalanceTotal = (float) Account::where('school_id', $schoolId)->where('balance', '<', 0)->sum('balance');

        // Real parent-confirmed choices for the week currently shown in the
        // planner — this is how the canteen is "informed" of an order: a
        // normal DB row, surfaced here rather than through a separate
        // notification channel. Scoped to the whole week (not just today):
        // the mobile app lets a parent confirm any day of the current week,
        // and a same-day-only filter would hide everything confirmed for a
        // different weekday, which is exactly what "today" narrowly missing
        // "Monday" looked like from the dashboard.
        $weekEnd = Carbon::parse($week)->endOfWeek(Carbon::SUNDAY)->format('Y-m-d');
        $todaysOrders = CanteenReservation::where('school_id', $schoolId)
            ->whereBetween('date', [$week, $weekEnd])
            ->with(['student.academicClass', 'menuItem'])
            ->get()
            ->filter(fn (CanteenReservation $r) => $r->student !== null)
            ->sortBy(fn (CanteenReservation $r) => $r->date->format('Y-m-d').'-'.$r->student->last_name)
            ->values();

        $weekdaysThisMonth = 0;
        $cursor = Carbon::now()->startOfMonth();
        while ($cursor->month === Carbon::now()->month) {
            if (!$cursor->isWeekend()) {
                $weekdaysThisMonth++;
            }
            $cursor->addDay();
        }

        return view('SchoolDashboard::canteen.reservations', compact(
            'accounts', 'classes', 'filters', 'dailyCounts', 'mealsToday', 'negativeBalanceTotal', 'weekdaysThisMonth', 'todaysOrders', 'week'
        ));
    }

    public function recordMeal(Request $request, RecordMealUseCase $useCase, AccountRepositoryInterface $accountRepository)
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:canteen_accounts,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
        ]);

        $accountRepository->find($data['account_id']);

        $useCase->execute(new CreateMealRecordDTO([
            'account_id' => $data['account_id'],
            'price' => $data['price'],
            'date' => $data['date'] ?? Carbon::today()->toDateString(),
        ]));

        return back()->with('success', 'Repas enregistré avec succès.');
    }

    public function exportRoster(AccountRepositoryInterface $accountRepository)
    {
        $accounts = Account::where('school_id', auth()->user()->school_id)->with('holder')->get();
        $statusLabels = ['demi_pensionnaire' => 'Demi-pensionnaire', 'externe' => 'Externe'];

        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=cantine_facturation_' . date('Y-m-d') . '.csv',
        ];

        $callback = function () use ($accounts, $statusLabels, $accountRepository) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Nom Complet', 'Groupe', 'Statut', 'Repas ce mois', 'Solde (FCFA)'], ',', '"', '\\');

            foreach ($accounts as $account) {
                $holder = $account->holder;
                if (!$holder) {
                    continue;
                }

                $group = $account->holder_type === 'student'
                    ? ($holder->academicClass->name ?? '-')
                    : 'Personnel';

                fputcsv($file, [
                    $holder->first_name . ' ' . $holder->last_name,
                    $group,
                    $statusLabels[$account->status] ?? $account->status,
                    $accountRepository->mealsCountThisMonth($account->id),
                    $account->balance,
                ], ',', '"', '\\');
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'cantine_facturation_' . date('Y-m-d') . '.csv', $headers);
    }

    public function creditAccount(Request $request, CreditAccountUseCase $useCase)
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:canteen_accounts,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
        ]);

        $useCase->execute($data['account_id'], (float) $data['amount']);

        return back()->with('success', 'Compte crédité avec succès.');
    }
}
