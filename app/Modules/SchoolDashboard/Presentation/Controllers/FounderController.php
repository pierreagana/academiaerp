<?php

namespace App\Modules\SchoolDashboard\Presentation\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Finance\Application\Services\StudentFeeService;
use Illuminate\Http\Request;

class FounderController extends Controller
{
    public function dashboard(Request $request, StudentFeeService $feeService)
    {
        $user = $request->user();
        abort_unless($user->isFounder(), 403, "Ce tableau de bord est réservé aux comptes Fondateur.");

        $schools = $user->foundedSchools()->with('branches')->get()->map(function ($school) use ($feeService) {
            $finance = $feeService->overallStats($school->id, 'tuition');

            return [
                'school' => $school,
                'students_count' => Student::where('school_id', $school->id)->count(),
                'teachers_count' => Teacher::where('school_id', $school->id)->count(),
                'branches_count' => $school->branches->count(),
                'totalCollected' => $finance['totalCollected'],
                'totalExpected' => $finance['totalExpected'],
                'collectionRate' => $finance['collectionRate'],
            ];
        });

        $totals = [
            'schools' => $schools->count(),
            'students' => $schools->sum('students_count'),
            'teachers' => $schools->sum('teachers_count'),
            'collected' => $schools->sum('totalCollected'),
        ];

        return view('SchoolDashboard::founder.dashboard', compact('schools', 'totals'));
    }
}
