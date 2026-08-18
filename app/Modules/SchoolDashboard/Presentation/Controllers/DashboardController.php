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

class DashboardController extends Controller
{
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
        return view('SchoolDashboard::dashboard.establishment', compact('school'));
    }

    public function editEstablishment()
    {
        $school = auth()->user()->school;
        return view('SchoolDashboard::dashboard.establishment_edit', compact('school'));
    }

    public function updateEstablishment(\Illuminate\Http\Request $request)
    {
        $school = auth()->user()->school;
        
        $request->validate([
            'name' => 'required|string|max:255',
            'slogan' => 'nullable|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'required|string|max:20',
            'location' => 'nullable|string|max:255',
            'logo' => 'nullable|image|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('logos', 'public');
            $school->logo_url = \Illuminate\Support\Facades\Storage::url($path);
        }

        $school->name = $request->name;
        $school->slogan = $request->slogan;
        $school->contact_email = $request->contact_email;
        $school->contact_phone = $request->contact_phone;
        $school->location = $request->location;
        $school->latitude = $request->latitude;
        $school->longitude = $request->longitude;
        $school->save();

        return redirect()->route('school.establishment')->with('success', 'Les informations de l\'établissement ont été mises à jour.');
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
