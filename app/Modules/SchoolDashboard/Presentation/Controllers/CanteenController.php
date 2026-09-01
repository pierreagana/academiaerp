<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Repositories\AcademicClassRepositoryInterface;
use App\Modules\Canteen\Application\DTOs\AdjustStockDTO;
use App\Modules\Canteen\Application\DTOs\CreateMealRecordDTO;
use App\Modules\Canteen\Application\DTOs\CreateMenuItemDTO;
use App\Modules\Canteen\Application\DTOs\CreateProductDTO;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Canteen\Application\Services\CanteenEnrollmentService;
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
use App\Modules\Canteen\Domain\Models\CanteenEnrollmentRequest;
use App\Modules\Canteen\Domain\Models\CanteenReservation;
use App\Modules\Canteen\Domain\Models\MealRecord;
use App\Modules\Canteen\Domain\Models\MenuItem;
use App\Modules\Canteen\Domain\Repositories\AccountRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MealRecordRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MenuRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\MenuTagRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\AllergenRepositoryInterface;
use App\Modules\Canteen\Domain\Repositories\ProductRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use RuntimeException;

class CanteenController extends Controller
{
    public function dashboard(CanteenStatsService $statsService, CanteenEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;

        $stats = $statsService->dashboardStats();
        $stats['enrolled_count'] = $enrollmentService->activeCount($schoolId);
        $criticalProducts = $statsService->criticalProducts();

        // Real weekday attendance (last 4 weeks of actual recorded meals) —
        // replaces a hardcoded ['LUN' => 70, 'MAR' => 55, ...] bar chart.
        $byWeekday = MealRecord::where('school_id', $schoolId)
            ->where('date', '>=', now()->subWeeks(4))
            ->get()
            ->groupBy(fn ($r) => $r->date->dayOfWeekIso); // 1=Mon..5=Fri

        $weekdayLabels = [1 => 'LUN', 2 => 'MAR', 3 => 'MER', 4 => 'JEU', 5 => 'VEN'];
        $weekdayCounts = [];
        foreach ($weekdayLabels as $iso => $label) {
            $weekdayCounts[$label] = $byWeekday->get($iso, collect())->count();
        }
        $maxWeekdayCount = max($weekdayCounts) ?: 1;
        $weekdayHeights = array_map(fn ($c) => $maxWeekdayCount > 0 ? round(($c / $maxWeekdayCount) * 100) : 0, $weekdayCounts);

        return view('SchoolDashboard::canteen.dashboard', compact(
            'stats', 'criticalProducts', 'weekdayCounts', 'weekdayHeights'
        ));
    }

    /**
     * Real attendance-pattern narration for the canteen dashboard — no more
     * fake "Score Végétal"/"Alerte Sodium" claims, which would need real
     * ingredient-level nutrition data that doesn't exist in this app.
     */
    public function aiCanteenInsight(CanteenStatsService $statsService, \App\Modules\SuperAdmin\Application\Services\AIService $aiService)
    {
        $schoolId = auth()->user()->school_id;

        $byWeekday = MealRecord::where('school_id', $schoolId)
            ->where('date', '>=', now()->subWeeks(4))
            ->get()
            ->groupBy(fn ($r) => $r->date->dayOfWeekIso);

        $weekdayLabels = [1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi', 5 => 'Vendredi'];
        $weekdayCounts = [];
        foreach ($weekdayLabels as $iso => $label) {
            $weekdayCounts[$label] = $byWeekday->get($iso, collect())->count();
        }

        $stats = $statsService->dashboardStats();
        $stats['enrolled_count'] = 0;

        $payload = [
            'repas_enregistres_par_jour_4_dernieres_semaines' => $weekdayCounts,
            'repas_aujourdhui' => $stats['meals_today'],
            'prix_moyen_aujourdhui_fcfa' => $stats['average_price_today'],
            'taux_gaspillage_mensuel_pct' => $stats['waste_ratio'],
            'produits_en_stock_critique' => $statsService->criticalProducts()->count(),
        ];

        if (array_sum($weekdayCounts) === 0) {
            return response()->json([
                'success' => false,
                'error' => "Pas encore assez de repas enregistrés pour dégager une tendance.",
                'stats' => $payload,
            ]);
        }

        $systemPrompt = "Tu es un assistant cantine scolaire pour AcademiaERP. Tu commentes des statistiques réelles de fréquentation et de gaspillage — jamais de prétendue analyse nutritionnelle, l'application ne dispose d'aucune donnée d'ingrédients.";
        $userPrompt = "Voici les statistiques réelles de la cantine :\n"
            . json_encode($payload, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un commentaire court (2-3 phrases) sur la fréquentation et le gaspillage, en français.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 200);

        return response()->json([
            'success' => $result['success'],
            'insight' => $result['text'],
            'error' => $result['error'],
            'stats' => $payload,
        ]);
    }

    /**
     * Real planning advisory from actual critical/expiring stock and how
     * many menu slots are still empty this week — no more generic
     * "produits locaux de saison" text with nothing behind it.
     */
    public function aiPlanningAdvice(
        Request $request,
        MenuRepositoryInterface $menuRepository,
        ProductRepositoryInterface $productRepository,
        \App\Modules\SuperAdmin\Application\Services\AIService $aiService
    ) {
        $week = $request->get('week', MenuItem::currentWeekStart()->format('Y-m-d'));

        $items = $menuRepository->itemsForWeek($week);
        $plannedCount = $items->count();

        $criticalProducts = $productRepository->criticalOrLow();
        $expiringSoon = $criticalProducts->filter(fn ($p) => $p->expiry_date && $p->expiry_date->lte(now()->addDays(14)));

        $payload = [
            'repas_deja_planifies_cette_semaine' => $plannedCount,
            'produits_stock_critique_ou_bas' => $criticalProducts->count(),
            'produits_bientot_perimes_14j' => $expiringSoon->map(fn ($p) => [
                'nom' => $p->name,
                'quantite' => $p->quantity,
                'unite' => $p->unit,
                'peremption' => $p->expiry_date?->format('d/m/Y'),
            ])->values()->toArray(),
        ];

        if ($criticalProducts->isEmpty() && $plannedCount === 0) {
            return response()->json([
                'success' => false,
                'error' => "Pas encore de menu planifié ni de produit en stock critique cette semaine.",
                'stats' => $payload,
            ]);
        }

        $systemPrompt = "Tu es un assistant cantine scolaire pour AcademiaERP. Tu donnes des conseils de planification de menu basés sur l'état réel du stock — jamais de recommandations diététiques ou de saisonnalité inventées, l'application n'a aucune donnée nutritionnelle ni de fournisseurs locaux.";
        $userPrompt = "Voici l'état réel du stock et de la planification :\n"
            . json_encode($payload, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un conseil court (2-3 phrases) en français : si des produits périment bientôt, recommande de les utiliser en priorité dans les prochains menus ; sinon dis simplement que le stock est sous contrôle.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 200);

        return response()->json([
            'success' => $result['success'],
            'advice' => $result['text'],
            'error' => $result['error'],
            'stats' => $payload,
        ]);
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

        $allProducts = $productRepository->criticalOrLow();
        $criticalCount = $allProducts->where('status', 'critical')->count();
        $lowStockCount = $allProducts->where('status', 'low_stock')->count();
        $expiringSoonCount = $allProducts->where('status', 'expiring_soon')->count();

        return view('SchoolDashboard::canteen.inventory', compact(
            'products', 'recentMovements', 'criticalCount', 'lowStockCount', 'expiringSoonCount'
        ));
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

    /**
     * Real order-volume narration from actual daily meal counts and account
     * balances — replaces a generic "anticipez les besoins" line with
     * nothing behind it.
     */
    public function aiReservationForecast(MealRecordRepositoryInterface $mealRepository, \App\Modules\SuperAdmin\Application\Services\AIService $aiService)
    {
        $schoolId = auth()->user()->school_id;
        $week = MenuItem::currentWeekStart()->format('Y-m-d');

        $dailyCounts = $mealRepository->dailyCountsForWeek($week);
        $mealsToday = $mealRepository->countToday();
        $negativeAccounts = Account::where('school_id', $schoolId)->where('balance', '<', 0)->count();
        $negativeBalanceTotal = (float) Account::where('school_id', $schoolId)->where('balance', '<', 0)->sum('balance');

        $payload = [
            'repas_par_jour_cette_semaine' => $dailyCounts,
            'repas_aujourdhui' => $mealsToday,
            'comptes_en_solde_negatif' => $negativeAccounts,
            'montant_total_solde_negatif_fcfa' => abs($negativeBalanceTotal),
        ];

        $totalMealsThisWeek = collect($dailyCounts)->sum(fn ($day) => is_array($day) ? ($day['count'] ?? 0) : $day);

        if ($totalMealsThisWeek === 0 && $negativeAccounts === 0) {
            return response()->json([
                'success' => false,
                'error' => "Pas encore assez de repas enregistrés cette semaine pour une analyse.",
                'stats' => $payload,
            ]);
        }

        $systemPrompt = "Tu es un assistant cantine scolaire pour AcademiaERP. Tu commentes des statistiques réelles de repas et de soldes de comptes — jamais de prédiction fabriquée.";
        $userPrompt = "Voici les données réelles de la semaine :\n"
            . json_encode($payload, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige un commentaire court (2-3 phrases) en français sur le volume de repas à prévoir et, s'il y en a, sur les comptes en solde négatif à relancer.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 200);

        return response()->json([
            'success' => $result['success'],
            'forecast' => $result['text'],
            'error' => $result['error'],
            'stats' => $payload,
        ]);
    }

    public function recordMeal(Request $request, RecordMealUseCase $useCase, AccountRepositoryInterface $accountRepository, CanteenEnrollmentService $enrollmentService)
    {
        $data = $request->validate([
            'account_id' => ['required', 'exists:canteen_accounts,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'date' => ['nullable', 'date'],
        ]);

        $account = $accountRepository->find($data['account_id']);

        // Gate on the account's own enrollment record — even the manual
        // roster-table form can't bypass the rule the new Scanner enforces.
        if ($account->holder_type === 'student' && !$enrollmentService->isEnrolled($account->holder_id)) {
            return back()->withErrors(['account_id' => "Cet élève n'a pas d'inscription cantine valide."]);
        }

        $useCase->execute(new CreateMealRecordDTO([
            'account_id' => $data['account_id'],
            'price' => $data['price'],
            'date' => $data['date'] ?? Carbon::today()->toDateString(),
        ]));

        return back()->with('success', 'Repas enregistré avec succès.');
    }

    public function enrollmentRequests(Request $request)
    {
        $schoolId = auth()->user()->school_id;
        $status = $request->get('status', 'pending');

        $requests = CanteenEnrollmentRequest::with(['student', 'requestedByParent', 'reviewedBy'])
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $enrolledIds = CanteenEnrollmentRequest::where('status', CanteenEnrollmentRequest::STATUS_APPROVED)
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')->from('canteen_enrollment_requests')->groupBy('student_id');
            })
            ->pluck('student_id');
        $unenrolledStudents = Student::where('school_id', $schoolId)
            ->whereNotIn('id', $enrolledIds)
            ->orderBy('first_name')
            ->get();

        return view('SchoolDashboard::canteen.requests', compact('requests', 'status', 'unenrolledStudents'));
    }

    public function directEnroll(Request $request, CanteenEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;

        $data = $request->validate([
            'student_ids' => ['required', 'array'],
            'student_ids.*' => ['integer', Rule::exists('students', 'id')->where('school_id', $schoolId)],
        ]);

        foreach ($data['student_ids'] as $studentId) {
            if (!$enrollmentService->isEnrolled($studentId)) {
                $enrollmentService->directlyEnroll(Student::findOrFail($studentId), $request->user());
            }
        }

        return back()->with('success', 'Élève(s) inscrit(s) à la cantine avec succès.');
    }

    public function approveEnrollment($id, CanteenEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $enrollmentRequest = CanteenEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->approve($enrollmentRequest, auth()->user());

        return back()->with('success', 'Inscription validée.');
    }

    public function rejectEnrollment(Request $request, $id, CanteenEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $data = $request->validate(['reason' => ['nullable', 'string', 'max:500']]);
        $enrollmentRequest = CanteenEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->reject($enrollmentRequest, auth()->user(), $data['reason'] ?? null);

        return back()->with('success', 'Demande refusée.');
    }

    public function withdrawEnrollment($id, CanteenEnrollmentService $enrollmentService)
    {
        $schoolId = auth()->user()->school_id;
        $enrollmentRequest = CanteenEnrollmentRequest::whereHas('student', fn ($q) => $q->where('school_id', $schoolId))->findOrFail($id);
        $enrollmentService->withdraw($enrollmentRequest, auth()->user());

        return back()->with('success', 'Élève retiré de la cantine.');
    }

    public function scanner()
    {
        $recentMeals = \App\Modules\Canteen\Domain\Models\MealRecord::with('account.holder')
            ->where('school_id', auth()->user()->school_id)
            ->whereDate('date', Carbon::today())
            ->latest()
            ->limit(15)
            ->get();

        return view('SchoolDashboard::canteen.scanner', compact('recentMeals'));
    }

    public function scan(Request $request, RecordMealUseCase $useCase, CanteenEnrollmentService $enrollmentService)
    {
        $data = $request->validate([
            'matricule' => ['required', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
        ]);

        $schoolId = auth()->user()->school_id;
        $student = Student::where('school_id', $schoolId)
            ->where('roll_number', trim($data['matricule']))
            ->where('status', 'active')
            ->first();

        if (!$student) {
            return back()->withErrors(['matricule' => "Élève introuvable pour ce matricule."])->withInput();
        }

        if (!$enrollmentService->isEnrolled($student->id)) {
            return back()->withErrors(['matricule' => "{$student->first_name} {$student->last_name} n'a pas d'inscription cantine valide."])->withInput();
        }

        $account = Account::where('school_id', $schoolId)
            ->where('holder_type', 'student')
            ->where('holder_id', $student->id)
            ->first();

        if (!$account) {
            return back()->withErrors(['matricule' => "Aucun compte cantine trouvé pour cet élève."])->withInput();
        }

        $useCase->execute(new CreateMealRecordDTO([
            'account_id' => $account->id,
            'price' => $data['price'],
            'date' => Carbon::today()->toDateString(),
        ]));

        return back()->with('success', "Repas enregistré pour {$student->first_name} {$student->last_name}.");
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
