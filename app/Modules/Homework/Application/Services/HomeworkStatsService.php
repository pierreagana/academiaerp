<?php

namespace App\Modules\Homework\Application\Services;

use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Models\HomeworkAttendance;
use App\Modules\Homework\Domain\Models\HomeworkSubmission;
use Illuminate\Support\Carbon;

class HomeworkStatsService
{
    /** Real active-student count for an assignment's class, and real remis count among them. */
    public function submissionProgress(HomeworkAssignment $assignment): array
    {
        $total = $assignment->academicClass->students()->where('status', 'active')->count();
        $remis = HomeworkSubmission::where('homework_assignment_id', $assignment->id)
            ->where('status', HomeworkSubmission::STATUS_SUBMITTED)
            ->count();

        return ['remis' => $remis, 'total' => $total];
    }

    /** Real derived label for a devoir maison card: Terminé (all graded) / Urgent (due within 24h) / En cours. */
    public function homeworkStatusLabel(HomeworkAssignment $assignment): string
    {
        $total = $assignment->academicClass->students()->where('status', 'active')->count();
        $graded = HomeworkSubmission::where('homework_assignment_id', $assignment->id)->whereNotNull('graded_at')->count();

        if ($total > 0 && $graded >= $total) {
            return 'termine';
        }

        $now = Carbon::now();
        if ($assignment->scheduled_at->lt($now)) {
            return 'en_retard';
        }

        if ($now->diffInHours($assignment->scheduled_at) <= 24) {
            return 'urgent';
        }

        return 'en_cours';
    }

    /** Scopes a query to one teacher's own assignments, or (when $teacher is null, e.g. an admin) the whole school's. */
    private function scopeOwner($query, ?Teacher $teacher, int $schoolId)
    {
        return $teacher ? $query->where('teacher_id', $teacher->id) : $query->where('school_id', $schoolId);
    }

    /** Real devoir-maison assignments for this teacher (or the whole school for an admin), most recent first, optionally filtered. */
    public function homeworkList(?Teacher $teacher, int $schoolId, ?int $classId, ?int $subjectId)
    {
        $query = $this->scopeOwner(HomeworkAssignment::query(), $teacher, $schoolId)
            ->where('type', HomeworkAssignment::TYPE_HOMEWORK)
            ->with(['academicClass', 'subject'])
            ->orderByDesc('scheduled_at');

        if ($classId) {
            $query->where('academic_class_id', $classId);
        }
        if ($subjectId) {
            $query->where('subject_id', $subjectId);
        }

        return $query->get();
    }

    /** Real submissions marked "remis" but not yet graded, across this teacher's (or the school's) assignments, most recently submitted first. */
    public function recentSubmissionsToGrade(?Teacher $teacher, int $schoolId, int $limit = 5)
    {
        return HomeworkSubmission::whereHas('assignment', fn ($q) => $this->scopeOwner($q, $teacher, $schoolId))
            ->where('status', HomeworkSubmission::STATUS_SUBMITTED)
            ->whereNull('graded_at')
            ->with(['student', 'assignment'])
            ->orderByDesc('submitted_at')
            ->limit($limit)
            ->get();
    }

    /** Real interrogation/contrôle assignments for this teacher (or the whole school for an admin), most recent first. */
    public function testsList(?Teacher $teacher, int $schoolId)
    {
        return $this->scopeOwner(HomeworkAssignment::query(), $teacher, $schoolId)
            ->where('type', HomeworkAssignment::TYPE_TEST)
            ->with(['academicClass', 'subject', 'room'])
            ->orderByDesc('scheduled_at')
            ->get();
    }

    /** Real assignments (both types) scheduled in the next 7 days for this teacher (or the school), soonest first. */
    public function upcomingWeek(?Teacher $teacher, int $schoolId, int $limit = 6)
    {
        return $this->scopeOwner(HomeworkAssignment::query(), $teacher, $schoolId)
            ->whereBetween('scheduled_at', [Carbon::now(), Carbon::now()->addDays(7)])
            ->with(['academicClass', 'subject'])
            ->orderBy('scheduled_at')
            ->limit($limit)
            ->get();
    }

    /** Real assignments with at least one ungraded "remis" submission, with real pending count. */
    public function toCorrect(?Teacher $teacher, int $schoolId, int $limit = 5)
    {
        return $this->scopeOwner(HomeworkAssignment::query(), $teacher, $schoolId)
            ->whereHas('submissions', fn ($q) => $q->where('status', HomeworkSubmission::STATUS_SUBMITTED)->whereNull('graded_at'))
            ->withCount(['submissions as pending_count' => fn ($q) => $q->where('status', HomeworkSubmission::STATUS_SUBMITTED)->whereNull('graded_at')])
            ->with(['academicClass', 'subject'])
            ->orderByDesc('scheduled_at')
            ->limit($limit)
            ->get();
    }

    /** Real live présents/absents/retards counts for an interrogation session. */
    public function liveAttendanceCounts(HomeworkAssignment $assignment): array
    {
        $records = HomeworkAttendance::where('homework_assignment_id', $assignment->id)->get();
        $total = $assignment->academicClass->students()->where('status', 'active')->count();

        return [
            'present' => $records->where('status', HomeworkAttendance::STATUS_PRESENT)->count(),
            'absent' => $records->where('status', HomeworkAttendance::STATUS_ABSENT)->count(),
            'late' => $records->where('status', HomeworkAttendance::STATUS_LATE)->count(),
            'total' => $total,
            'marked' => $records->count(),
        ];
    }
}
