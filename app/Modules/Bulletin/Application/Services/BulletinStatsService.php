<?php

namespace App\Modules\Bulletin\Application\Services;

use App\Modules\Academic\Domain\Models\AcademicClass;
use App\Modules\Academic\Domain\Models\Semester;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Bulletin\Domain\Models\BulletinEvaluationType;
use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\Bulletin\Domain\Repositories\BulletinSubjectPublicationRepositoryInterface;
use App\Modules\Homework\Domain\Models\HomeworkAssignment;
use App\Modules\Homework\Domain\Models\HomeworkSubmission;

class BulletinStatsService
{
    private const SIGNIFICANT_GAP_THRESHOLD = 4.0;

    public function currentSemester(int $schoolId): ?Semester
    {
        return Semester::where('school_id', $schoolId)->where('is_current', true)->first()
            ?? Semester::where('school_id', $schoolId)->latest('start_date')->first();
    }

    public function previousSemester(int $schoolId, ?Semester $current): ?Semester
    {
        if (!$current) {
            return null;
        }

        return Semester::where('school_id', $schoolId)
            ->where('start_date', '<', $current->start_date)
            ->orderByDesc('start_date')
            ->first();
    }

    /** Deterministic score-band remark suggestion — never labelled as AI-generated. */
    public function suggestedRemark(float $score): string
    {
        return match (true) {
            $score >= 16 => 'Excellent travail, continuez ainsi.',
            $score >= 14 => 'Bon trimestre, bonne maîtrise des notions.',
            $score >= 12 => 'Assez bien, des efforts à poursuivre.',
            $score >= 10 => 'Résultat passable, doit approfondir le travail personnel.',
            default => 'Résultat insuffisant, un accompagnement est nécessaire.',
        };
    }

    /** Real weighted moyenne for one subject, from its raw evaluation-type rows — Σ(score×type.coefficient)/Σ(type.coefficient), over whichever types have actually been entered so far (an honest partial average, not blocked until every type is filled). */
    public function subjectMoyenne($rowsForOneSubject): ?float
    {
        $totalWeight = 0;
        $totalScore = 0;

        foreach ($rowsForOneSubject as $row) {
            $coef = (float) ($row->evaluationType->coefficient ?? 1);
            $totalScore += $row->score * $coef;
            $totalWeight += $coef;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : null;
    }

    /** Condenses raw per-evaluation-type BulletinGrade rows into one pseudo-grade per (student, subject), ->score = subjectMoyenne(). Feed this into studentAverage()/classRanking()/etc instead of raw rows. Remark is taken from whichever entered row has the highest evaluation-type coefficient (falls back to any non-null remark). */
    public function aggregateToSubjectGrades($rawGrades)
    {
        return $rawGrades
            ->groupBy(fn ($g) => $g->student_id . '-' . $g->subject_id)
            ->map(function ($rows) {
                $first = $rows->first();
                $remark = $rows->sortByDesc(fn ($r) => (float) ($r->evaluationType->coefficient ?? 1))
                    ->first(fn ($r) => !empty($r->remark))?->remark;

                return (object) [
                    'student_id' => $first->student_id,
                    'subject_id' => $first->subject_id,
                    'subject' => $first->subject,
                    'student' => $first->student ?? null,
                    'teacher' => $first->teacher ?? null,
                    'score' => $this->subjectMoyenne($rows),
                    'remark' => $remark,
                ];
            })
            ->filter(fn ($g) => $g->score !== null)
            ->values();
    }

    /**
     * Merges real graded scores from the Homework module (devoirs maison / interrogations
     * already tracked there) into a raw BulletinGrade collection, as synthetic rows shaped
     * the same way (student_id, subject_id, evaluation_type_id, evaluationType, score, remark),
     * for every BulletinEvaluationType of this school that has linked_homework_type set.
     * Call this right after fetching raw grades and before aggregateToSubjectGrades() —
     * avoids double entry: a teacher grades homework once, in the Homework module.
     */
    public function mergeHomeworkGrades($rawBulletinGrades, int $academicClassId, int $semesterId, ?int $subjectId = null, ?int $schoolId = null)
    {
        $schoolId ??= auth()->user()?->school_id;
        $linkedTypes = BulletinEvaluationType::where('school_id', $schoolId)->whereNotNull('linked_homework_type')->get();

        if ($linkedTypes->isEmpty()) {
            return $rawBulletinGrades;
        }

        $homeworkRows = collect();

        foreach ($linkedTypes as $type) {
            $submissions = HomeworkSubmission::whereNotNull('score')
                ->whereHas('assignment', function ($q) use ($academicClassId, $semesterId, $subjectId, $type) {
                    $q->where('academic_class_id', $academicClassId)
                        ->where('semester_id', $semesterId)
                        ->where('type', $type->linked_homework_type);
                    if ($subjectId) {
                        $q->where('subject_id', $subjectId);
                    }
                    // Only assignments explicitly linked to THIS evaluation type count towards the bulletin
                    $q->where('evaluation_type_id', $type->id);
                })
                ->with(['student', 'assignment.subject', 'assignment.teacher'])
                ->get();

            foreach ($submissions as $submission) {
                $maxScore = (float) ($submission->assignment->max_score ?: 20);
                $normalizedScore = $maxScore > 0 ? round($submission->score / $maxScore * 20, 2) : $submission->score;

                $homeworkRows->push((object) [
                    'student_id' => $submission->student_id,
                    'subject_id' => $submission->assignment->subject_id,
                    'subject' => $submission->assignment->subject,
                    'student' => $submission->student,
                    'teacher' => $submission->assignment->teacher,
                    'score' => $normalizedScore,
                    'raw_score' => $submission->score,
                    'max_score' => $maxScore,
                    'remark' => $submission->feedback,
                    'evaluation_type_id' => $type->id,
                    'evaluationType' => $type,
                    'homework_assignment_id' => $submission->homework_assignment_id,
                ]);
            }
        }

        return $rawBulletinGrades->concat($homeworkRows);
    }

    /**
     * One display column per real HomeworkAssignment matching a linked evaluation type's
     * class/semester/(subject)/type — even assignments nobody has graded yet, so the grade
     * entry grid always shows every devoir/interrogation the teacher created, not just the
     * graded ones. Falls back to a single placeholder column (keyed by the type itself) when
     * a linked type has no matching assignment at all, preserving the old aggregated look.
     */
    public function linkedHomeworkColumns(int $schoolId, int $academicClassId, int $semesterId, ?int $subjectId = null)
    {
        $linkedTypes = BulletinEvaluationType::where('school_id', $schoolId)->whereNotNull('linked_homework_type')->get();

        $columns = collect();
        foreach ($linkedTypes as $type) {
            $assignments = HomeworkAssignment::where('academic_class_id', $academicClassId)
                ->where('semester_id', $semesterId)
                ->where('type', $type->linked_homework_type)
                ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
                // Only show columns for assignments that are explicitly linked to this evaluation type
                ->where('evaluation_type_id', $type->id)
                ->orderBy('scheduled_at')
                ->get();

            if ($assignments->isEmpty()) {
                $columns->push((object) [
                    'key' => 'type_' . $type->id,
                    'label' => $type->name,
                    'type' => $type,
                    'assignment' => null,
                    'linked' => true,
                ]);
                continue;
            }

            foreach ($assignments as $assignment) {
                $columns->push((object) [
                    'key' => 'hw_' . $assignment->id,
                    'label' => $assignment->title,
                    'type' => $type,
                    'assignment' => $assignment,
                    'linked' => true,
                ]);
            }
        }

        return $columns;
    }

    /**
     * Keeps only rows for subjects whose own teacher has published their grades for this
     * class+semester (BulletinSubjectPublication) — a subject with real grades entered but
     * not yet published stays absent here, so it shows blank on the printed bulletin and
     * doesn't count toward the moyenne générale/rang until its teacher publishes it.
     */
    public function filterPublishedSubjects($grades, int $academicClassId, int $semesterId)
    {
        $publishedSubjectIds = app(BulletinSubjectPublicationRepositoryInterface::class)->publishedSubjectIds($academicClassId, $semesterId);

        return $grades->whereIn('subject_id', $publishedSubjectIds)->values();
    }

    /** Weighted average (Σ score×coef / Σ coef) for one student in one semester, from real per-subject moyennes + real Subject::coefficient. Expects grades already condensed via aggregateToSubjectGrades(). */
    public function studentAverage($grades): ?float
    {
        $totalWeight = 0;
        $totalScore = 0;

        foreach ($grades as $grade) {
            $coef = (float) ($grade->subject->coefficient ?? 1);
            $totalScore += $grade->score * $coef;
            $totalWeight += $coef;
        }

        return $totalWeight > 0 ? round($totalScore / $totalWeight, 2) : null;
    }

    /** Real class ranking: every active student in the class, ordered by weighted average (nulls last). */
    public function classRanking(int $academicClassId, int $semesterId, $classGrades): array
    {
        $students = Student::where('academic_class_id', $academicClassId)->where('status', 'active')->with('academicClass.subjects')->get();
        $classGrades = $this->aggregateToSubjectGrades($classGrades);

        $rows = $students->map(function ($student) use ($classGrades) {
            $studentGrades = $classGrades->where('student_id', $student->id);
            $average = $this->studentAverage($studentGrades);
            $expectedSubjects = $student->academicClass?->subjects->count() ?? 0;
            $gradedSubjects = $studentGrades->count();

            $status = 'En attente';
            if ($gradedSubjects > 0 && $expectedSubjects > 0) {
                $status = $gradedSubjects >= $expectedSubjects ? 'Validé' : 'À revoir';
            }

            return [
                'student' => $student,
                'average' => $average,
                'graded_subjects' => $gradedSubjects,
                'expected_subjects' => $expectedSubjects,
                'status' => $status,
            ];
        })->sortByDesc(fn ($row) => $row['average'] ?? -1)->values();

        return $rows->map(function ($row, $index) {
            $row['rank'] = $row['average'] !== null ? $index + 1 : null;
            return $row;
        })->all();
    }

    /** Real class average for one subject (average of real per-student weighted moyennes among the class this semester). Accepts raw (per-evaluation-type) rows — aggregates internally. */
    public function subjectClassAverage($classGrades, int $subjectId): ?float
    {
        $scores = $this->aggregateToSubjectGrades($classGrades)->where('subject_id', $subjectId)->pluck('score');

        return $scores->isNotEmpty() ? round($scores->avg(), 2) : null;
    }

    /** Real rank of one student within their class for one subject, among every classmate with a real score for it (nulls excluded, ties share no special handling — plain descending sort position). */
    public function subjectRank($classGrades, int $subjectId, int $studentId): ?array
    {
        $subjectGrades = $this->aggregateToSubjectGrades($classGrades)->where('subject_id', $subjectId)->sortByDesc('score')->values();
        $total = $subjectGrades->count();
        $index = $subjectGrades->search(fn ($g) => $g->student_id === $studentId);

        return $index !== false ? ['rank' => $index + 1, 'total' => $total] : null;
    }

    public function subjectHighestLowest($classGrades, int $subjectId): array
    {
        $scores = $this->aggregateToSubjectGrades($classGrades)->where('subject_id', $subjectId)->pluck('score');

        return [
            'highest' => $scores->isNotEmpty() ? $scores->max() : null,
            'lowest' => $scores->isNotEmpty() ? $scores->min() : null,
        ];
    }

    /** Real signal: students whose own subject scores this semester span more than the threshold. */
    public function significantGaps(int $schoolId, ?int $semesterId): array
    {
        if (!$semesterId) {
            return [];
        }

        $rawGrades = BulletinGrade::where('semester_id', $semesterId)
            ->whereHas('student', fn ($q) => $q->where('school_id', $schoolId))
            ->with(['student.academicClass', 'subject', 'evaluationType'])
            ->get();

        $subjectGrades = $this->aggregateToSubjectGrades($rawGrades)->groupBy('student_id');

        $flagged = [];
        foreach ($subjectGrades as $studentGrades) {
            if ($studentGrades->count() < 2) {
                continue;
            }
            $max = $studentGrades->max('score');
            $min = $studentGrades->min('score');
            $gap = $max - $min;

            if ($gap > self::SIGNIFICANT_GAP_THRESHOLD) {
                $flagged[] = [
                    'student' => $studentGrades->first()->student,
                    'gap' => round($gap, 1),
                ];
            }
        }

        usort($flagged, fn ($a, $b) => $b['gap'] <=> $a['gap']);

        return $flagged;
    }

    /** Real signal: class+subject pairs where less than 100% of active students have a grade this semester. */
    public function incompleteEntries(int $schoolId, ?int $semesterId): array
    {
        if (!$semesterId) {
            return [];
        }

        $classes = AcademicClass::where('school_id', $schoolId)->with(['subjects', 'students' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $incomplete = [];
        foreach ($classes as $class) {
            $activeCount = $class->students->count();
            if ($activeCount === 0) {
                continue;
            }

            foreach ($class->subjects as $subject) {
                $gradedCount = BulletinGrade::where('semester_id', $semesterId)
                    ->where('subject_id', $subject->id)
                    ->whereIn('student_id', $class->students->pluck('id'))
                    ->distinct('student_id')
                    ->count('student_id');

                if ($gradedCount < $activeCount) {
                    $incomplete[] = [
                        'class' => $class->name,
                        'subject' => $subject->name,
                        'graded' => $gradedCount,
                        'expected' => $activeCount,
                    ];
                }
            }
        }

        return $incomplete;
    }

    /** Real % of expected (student × taught-subject) grade slots filled this semester, school-wide. */
    public function gradesEnteredRate(int $schoolId, ?int $semesterId): ?float
    {
        if (!$semesterId) {
            return null;
        }

        $classes = AcademicClass::where('school_id', $schoolId)->with(['subjects', 'students' => function ($q) {
            $q->where('status', 'active');
        }])->get();

        $expected = 0;
        $filled = 0;

        foreach ($classes as $class) {
            $activeCount = $class->students->count();
            foreach ($class->subjects as $subject) {
                $expected += $activeCount;
                $filled += BulletinGrade::where('semester_id', $semesterId)
                    ->where('subject_id', $subject->id)
                    ->whereIn('student_id', $class->students->pluck('id'))
                    ->distinct('student_id')
                    ->count('student_id');
            }
        }

        return $expected > 0 ? round($filled / $expected * 100) : null;
    }

    /** Semesters sharing the same academic_year as $semester, ordered by term_number. Falls back to just [$semester] when academic_year isn't configured — this whole annual-average feature is opt-in. */
    public function academicYearSemesters(int $schoolId, Semester $semester)
    {
        if (!$semester->academic_year) {
            return collect([$semester]);
        }

        return Semester::where('school_id', $schoolId)
            ->where('academic_year', $semester->academic_year)
            ->orderBy('term_number')
            ->get();
    }

    /** True only when $semester is genuinely the last of at least 3 real trimesters configured for its academic_year — never guessed. */
    public function isLastTermOfYear(int $schoolId, Semester $semester): bool
    {
        if (!$semester->academic_year || !$semester->term_number) {
            return false;
        }

        $siblings = $this->academicYearSemesters($schoolId, $semester);
        if ($siblings->count() < 3) {
            return false;
        }

        return (int) $semester->term_number === (int) $siblings->max('term_number');
    }

    /** Real weighted average for one student for one specific semester (their own class, merged with real Homework scores). */
    public function studentAverageForSemester(Student $student, Semester $semester): ?float
    {
        if (!$student->academic_class_id) {
            return null;
        }

        $rawGrades = app(\App\Modules\Bulletin\Domain\Repositories\BulletinGradeRepositoryInterface::class)
            ->forClassAndSemester($student->academic_class_id, $semester->id)
            ->where('student_id', $student->id);

        $merged = $this->mergeHomeworkGrades($rawGrades, $student->academic_class_id, $semester->id, null, $student->school_id);
        $merged = $this->filterPublishedSubjects($merged, $student->academic_class_id, $semester->id);

        return $this->studentAverage($this->aggregateToSubjectGrades($merged));
    }

    /** Final annual average (T1+T2+T3)/3 — only when the student has a real computed average for every configured term of the year, never a partial value presented as final. */
    public function annualFinalAverage(int $schoolId, Student $student, Semester $lastSemester): ?float
    {
        if (!$this->isLastTermOfYear($schoolId, $lastSemester)) {
            return null;
        }

        $averages = $this->academicYearSemesters($schoolId, $lastSemester)
            ->map(fn ($sem) => $this->studentAverageForSemester($student, $sem));

        if ($averages->contains(null)) {
            return null;
        }

        return round($averages->avg(), 2);
    }

    /** Real % of classes at/above each publication milestone this semester. */
    public function publicationRates(int $schoolId, ?int $semesterId): array
    {
        if (!$semesterId) {
            return ['validated' => null, 'published' => null];
        }

        $classIds = AcademicClass::where('school_id', $schoolId)->pluck('id');
        $total = $classIds->count();
        if ($total === 0) {
            return ['validated' => null, 'published' => null];
        }

        $publications = \App\Modules\Bulletin\Domain\Models\BulletinPublication::where('semester_id', $semesterId)
            ->whereIn('academic_class_id', $classIds)
            ->get();

        $validated = $publications->whereIn('status', ['validated', 'published'])->count();
        $published = $publications->where('status', 'published')->count();

        return [
            'validated' => round($validated / $total * 100),
            'published' => round($published / $total * 100),
        ];
    }
}
