<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Branch;
use App\Modules\Communication\Domain\Repositories\EventRepositoryInterface;
use App\Modules\Finance\Application\Services\StudentFeeService;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\SchoolDashboard\Application\Services\TeacherDashboardService;
use App\Modules\SuperAdmin\Application\Services\AIService;

class DashboardController extends Controller
{
    /**
     * Real dashboard insights, narrated by AI — replaces the "Assistant
     * d'Aperçus IA" panel that used to show 3 hardcoded fabricated cards
     * (a fake -5% attendance drop, a fake "Labo de Science 2" room
     * suggestion, a fake fixed "95%" fee prediction) under a header that
     * literally named the wrong school ("Nairobi West International
     * School") for every single tenant on the platform.
     */
    public function aiInsights(StudentFeeService $feeService, AIService $aiService)
    {
        $school = auth()->user()->school;
        $schoolId = $school->id;
        $branchId = auth()->user()->activeBranchId();

        // Real attendance trend: this month vs last month, absence rate.
        $thisMonthRecords = AttendanceRecord::where('school_id', $schoolId)->whereBranch($branchId)
            ->where('date', '>=', now()->startOfMonth())
            ->get();
        $lastMonthRecords = AttendanceRecord::where('school_id', $schoolId)->whereBranch($branchId)
            ->whereBetween('date', [now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()])
            ->get();

        $absenceRate = fn ($records) => $records->count() > 0
            ? round(($records->where('status', AttendanceRecord::STATUS_ABSENT)->count() / $records->count()) * 100, 1)
            : null;

        $thisMonthAbsenceRate = $absenceRate($thisMonthRecords);
        $lastMonthAbsenceRate = $absenceRate($lastMonthRecords);

        // Real room usage: weekly timetable slots per room, from actual data.
        $roomUsage = \App\Modules\Academic\Domain\Models\Timetable::whereHas('academicClass', fn ($q) => $q->where('school_id', $schoolId))
            ->whereNotNull('room_id')
            ->with('room')
            ->get()
            ->groupBy('room_id')
            ->map(fn ($slots) => ['room' => $slots->first()->room?->name ?? 'Salle', 'creneaux' => $slots->count()])
            ->sortBy('creneaux')
            ->values();

        // Real fee collection rate (already computed for the main dashboard KPIs).
        $financeStats = $feeService->overallStats($schoolId);

        $payload = [
            'ecole' => $school->name,
            'taux_absence_ce_mois_pct' => $thisMonthAbsenceRate,
            'taux_absence_mois_dernier_pct' => $lastMonthAbsenceRate,
            'salle_la_moins_utilisee' => $roomUsage->first()['room'] ?? null,
            'creneaux_hebdo_salle_la_moins_utilisee' => $roomUsage->first()['creneaux'] ?? null,
            'taux_recouvrement_frais_pct' => $financeStats['collectionRate'],
        ];

        $systemPrompt = "Tu es un assistant de direction scolaire pour AcademiaERP. Tu résumes des indicateurs réels de l'établissement \"{$school->name}\" en français, de façon factuelle — jamais de chiffre inventé, jamais le nom d'un autre établissement.";
        $userPrompt = "Voici les indicateurs réels de l'établissement (données SQL) :\n"
            . json_encode($payload, JSON_UNESCAPED_UNICODE)
            . "\n\nRédige 2 à 3 observations courtes (une phrase chacune) en français : assiduité, utilisation des salles si pertinent, recouvrement des frais. N'invente rien qui ne soit pas dans ces données.";

        $result = $aiService->generateText($systemPrompt, $userPrompt, 260);

        return response()->json([
            'success' => $result['success'],
            'insights' => $result['text'],
            'error' => $result['error'],
            'stats' => $payload,
        ]);
    }

    public function index(StudentFeeService $feeService, EventRepositoryInterface $eventRepository, TeacherDashboardService $teacherDashboard)
    {
        if (auth()->user()->teacher) {
            return $this->teacherIndex($teacherDashboard);
        }

        $school = auth()->user()->school;
        $branchId = auth()->user()->activeBranchId();
        $activeBranch = $branchId ? Branch::find($branchId) : null;
        $branches = Branch::where('school_id', $school->id)->orderByDesc('is_main')->orderBy('name')->get();

        $stats = [
            'total_students' => Student::where('school_id', $school->id)->whereBranch($branchId)->count(),
            'total_teachers' => Teacher::where('school_id', $school->id)->whereBranch($branchId)->count(),
            'total_staff' => Staff::where('school_id', $school->id)->whereBranch($branchId)->count(),
        ];

        $todayRecords = AttendanceRecord::where('school_id', $school->id)->whereBranch($branchId)->where('date', now()->toDateString())->get();
        $attendanceStats = [
            'present' => $todayRecords->where('status', AttendanceRecord::STATUS_PRESENT)->count(),
            'absent' => $todayRecords->where('status', AttendanceRecord::STATUS_ABSENT)->count(),
            'late' => $todayRecords->where('status', AttendanceRecord::STATUS_LATE)->count(),
            'recorded' => $todayRecords->count(),
        ];

        $financeStats = $feeService->overallStats($school->id);
        $overdueStats = $feeService->overdueStats($school->id);
        $upcomingEvents = $eventRepository->upcoming(3);

        return view('SchoolDashboard::dashboard.index', compact(
            'school', 'stats', 'attendanceStats', 'financeStats', 'overdueStats', 'upcomingEvents', 'activeBranch', 'branches'
        ));
    }

    private function teacherIndex(TeacherDashboardService $teacherDashboard)
    {
        $user = auth()->user();
        $teacher = $user->teacher;
        $schoolId = $user->school_id;

        $currentSemester = $teacherDashboard->currentSemester($schoolId);
        $previousSemester = $teacherDashboard->previousSemester($schoolId, $currentSemester);

        $myClasses = $teacherDashboard->myClasses($teacher);
        $classAverage = $teacherDashboard->classAverage($teacher, $currentSemester?->id);
        $classAverageTrend = $teacherDashboard->classAverageTrend($teacher, $currentSemester?->id, $previousSemester?->id);
        $attendanceRate = $teacherDashboard->attendanceRate($teacher);
        $gradesToEnter = $teacherDashboard->gradesToEnter($teacher, $currentSemester?->id);
        $averageDropAlerts = $teacherDashboard->averageDropAlerts($teacher, $currentSemester?->id, $previousSemester?->id);
        $repeatedAbsenceAlerts = $teacherDashboard->repeatedAbsenceAlerts($teacher);
        $recentGradeEntries = $teacherDashboard->recentGradeEntries($teacher, $currentSemester?->id);
        $todaySchedule = $teacherDashboard->todaySchedule($teacher);

        return view('SchoolDashboard::dashboard.teacher_index', compact(
            'teacher', 'currentSemester', 'myClasses', 'classAverage', 'classAverageTrend', 'attendanceRate',
            'gradesToEnter', 'averageDropAlerts', 'repeatedAbsenceAlerts', 'recentGradeEntries', 'todaySchedule'
        ));
    }

    public function establishment()
    {
        $school = auth()->user()->school;
        if ($school) {
            $school->load('facilitiesList');
        }
        return view('SchoolDashboard::dashboard.establishment', compact('school'));
    }

    public function editEstablishment()
    {
        $school = auth()->user()->school;
        $facilities = \App\Modules\SuperAdmin\Domain\Models\Facility::where('is_active', true)->orderBy('order')->orderBy('name')->get();
        $selectedFacilityIds = $school ? $school->facilitiesList->pluck('id')->all() : [];

        $availableSectors = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableSectors();
        $availableLevels = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableLevels();
        $availableLanguageRegimes = \App\Modules\SuperAdmin\Domain\Models\School::getAvailableLanguageRegimes();

        return view('SchoolDashboard::dashboard.establishment_edit', compact(
            'school', 'facilities', 'selectedFacilityIds',
            'availableSectors', 'availableLevels', 'availableLanguageRegimes'
        ));
    }

    public function updateEstablishment(\Illuminate\Http\Request $request)
    {
        $school = auth()->user()->school;
        
        $request->validate([
            'name'            => 'required|string|max:255',
            'slogan'          => 'nullable|string|max:255',
            'sector'          => 'nullable|string|max:100',
            'is_bilingual'    => 'nullable|boolean',
            'language_regime' => 'nullable|string|max:100',
            'levels'          => 'nullable|array',
            'levels.*'        => 'string|max:100',
            'contact_email'   => 'required|email|max:255',
            'phone_country_code' => 'nullable|string',
            'phone_number'    => 'required|string|max:20',
            'location'        => 'nullable|string|max:255',
            'logo'            => 'nullable|image|max:2048',
            'facilities'      => 'nullable|array',
            'facilities.*'    => 'integer|exists:facilities,id',
            'day_start_time'  => 'nullable|date_format:H:i',
            'day_end_time'    => 'nullable|date_format:H:i|after:day_start_time',
            'catalog'         => ['nullable', 'array', function ($attribute, $value, $fail) use ($request, $school) {
                $keptCount = collect($school->catalog_paths ?? [])
                    ->reject(fn ($path) => in_array($path, $request->input('remove_catalog', []), true))
                    ->count();
                if ($keptCount + count($value) > \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS) {
                    $fail('Le catalogue photos ne peut pas dépasser ' . \App\Modules\SuperAdmin\Domain\Models\School::CATALOG_MAX_PHOTOS . ' images.');
                }
            }],
            // Must stay at or under php.ini's upload_max_filesize (currently
            // 10M) — a larger file gets silently dropped by PHP before
            // Laravel ever sees it, so this rule is the only thing that can
            // actually show the user an error instead of nothing happening.
            'catalog.*'       => 'image|max:10240',
            'remove_catalog'  => 'nullable|array',
            'remove_catalog.*' => 'string',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            // Must be the 'public' disk's own url() — the bare Storage::url()
            // facade call uses the default disk (FILESYSTEM_DISK=local),
            // which has no APP_URL-prefixed 'url' config and so silently
            // returns a host-less "/storage/..." path instead of a real URL.
            $school->logo_url = \Illuminate\Support\Facades\Storage::disk('public')->url($path);
        }

        $isBilingual = $request->has('language_regime')
            ? str_contains(strtolower($request->input('language_regime')), 'bilingue')
            : (bool) $request->input('is_bilingual', false);

        $school->name = $request->name;
        $school->slogan = $request->slogan;
        $school->sector = $request->sector ?? $school->sector;
        $school->is_bilingual = $isBilingual;
        $school->language_regime = $request->language_regime ?? $school->language_regime;
        $school->levels = $request->input('levels', $school->levels ?? []);
        $school->contact_email = $request->contact_email;
        $school->contact_phone = \App\Modules\SuperAdmin\Domain\Models\Country::combinePhone($request->phone_country_code, $request->phone_number);
        $school->location = $request->location;
        $school->latitude = $request->latitude;
        $school->longitude = $request->longitude;
        $school->day_start_time = $request->day_start_time;
        $school->day_end_time = $request->day_end_time;

        $existingCatalog = collect($school->catalog_paths ?? []);
        $toRemove = collect($request->input('remove_catalog', []));
        foreach ($toRemove as $path) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($path);
        }
        $keptCatalog = $existingCatalog->reject(fn ($path) => $toRemove->contains($path));

        $newCatalogPaths = collect();
        if ($request->hasFile('catalog')) {
            foreach ($request->file('catalog') as $file) {
                $newCatalogPaths->push($file->store('school_catalog', 'public'));
            }
        }
        $school->catalog_paths = $keptCatalog->concat($newCatalogPaths)->values()->all();

        $school->save();

        if ($request->has('facilities')) {
            $school->facilitiesList()->sync($request->input('facilities', []));
        } else {
            $school->facilitiesList()->sync([]);
        }

        return redirect()->route('school.establishment')->with('success', 'Les informations et équipements de l\'établissement ont été mis à jour.');
    }

    public function profile()
    {
        $user = auth()->user();
        return view('SchoolDashboard::dashboard.profile', compact('user'));
    }

    public function updateProfile(\Illuminate\Http\Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'current_password' => 'nullable|string|min:8',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;

        if ($request->filled('new_password')) {
            if (!\Illuminate\Support\Facades\Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
            }
            $user->password = \Illuminate\Support\Facades\Hash::make($request->new_password);
        }

        $user->save();

        return redirect()->route('school.profile')->with('success', 'Profil mis à jour avec succès.');
    }
}
