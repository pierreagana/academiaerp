<?php

namespace Database\Seeders;

use App\Modules\Academic\Domain\Models\Guardian;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Bulletin\Domain\Models\BulletinEvaluationType;
use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\Bulletin\Domain\Models\BulletinPublication;
use App\Modules\Bulletin\Domain\Models\BulletinSubjectPublication;
use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Finance\Domain\Models\FeeLevel;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\ReportCard\Domain\Models\ReportCardObservation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

/**
 * Fills in the real tables that were empty for the demo parent (0102723408) /
 * child (Amadou Diallo, id 1) pair used throughout mobile-app verification —
 * grades, attendance, infirmary visits, behaviour notes, events — plus a
 * second child (Fatou Diallo) attached to the same real Guardian record, so
 * the multi-child parent-portal path has real data to exercise too. Every
 * insert is an updateOrCreate/firstOrCreate keyed on natural fields, so
 * re-running this seeder is safe.
 */
class DemoParentDataSeeder extends Seeder
{
    public function run(): void
    {
        $schoolId = 9;
        $branchId = 9;
        $teacherId = 1;
        $recordedByUserId = 18; // Tux root, the real teacher portal account
        $adminUserId = 3; // Directeur Dakar, used elsewhere as validated_by/published_by

        $guardian = Guardian::where('phone', '0102723408')->firstOrFail();
        $child1 = Student::findOrFail(1); // Amadou Diallo, 6ème A (class 1)

        $child2 = Student::updateOrCreate(
            ['school_id' => $schoolId, 'roll_number' => 'STU-0002'],
            [
                'first_name' => 'Fatou',
                'last_name' => 'Diallo',
                'dob' => '2013-05-14',
                'gender' => 'female',
                'academic_class_id' => 2, // 5ème A
                'academic_year' => '2025-2026',
                'status' => 'active',
            ]
        );

        $guardian->students()->syncWithoutDetaching([$child1->id, $child2->id]);

        $semesterId = 1; // Semestre 1, current

        // --- Bulletin grades -------------------------------------------------
        $evalTypes = BulletinEvaluationType::where('school_id', $schoolId)->get()->keyBy('name');
        $devoirSurveille = $evalTypes['Devoir surveillé'];
        $composition = $evalTypes['Composition'];
        $interrogation = $evalTypes['Interrogation'];

        // Child 1 / Maths (subject 1): only DS + Composition — "Interrogation" for
        // this subject is already synthesized from the real HomeworkSubmission rows.
        $this->grade($child1->id, 1, $semesterId, $teacherId, $devoirSurveille->id, 16.0, 'Bonne maîtrise des notions, calcul mental à consolider.');
        $this->grade($child1->id, 1, $semesterId, $teacherId, $composition->id, 14.5, 'Trimestre correct, quelques erreurs d\'inattention.');

        // Child 1 / Français (subject 2): no homework rows exist for this subject, so all 3 types are real entries.
        $this->grade($child1->id, 2, $semesterId, $teacherId, $interrogation->id, 12.0, 'Participation à l\'oral encourageante.');
        $this->grade($child1->id, 2, $semesterId, $teacherId, $devoirSurveille->id, 14.0, 'Rédaction bien structurée.');
        $this->grade($child1->id, 2, $semesterId, $teacherId, $composition->id, 13.0, 'Doit enrichir son vocabulaire.');

        // Child 2 / Maths (subject 1) — no timetable/homework configured yet for class 2, so all 3 types are real entries.
        $this->grade($child2->id, 1, $semesterId, $teacherId, $interrogation->id, 18.0, 'Excellente élève, très rigoureuse.');
        $this->grade($child2->id, 1, $semesterId, $teacherId, $devoirSurveille->id, 17.0, 'Très bon niveau, continue ainsi.');
        $this->grade($child2->id, 1, $semesterId, $teacherId, $composition->id, 16.5, 'Résultat solide sur l\'ensemble du programme.');

        // --- Publish gates (class-level + subject-level, matching the real two-tier gate) ---
        BulletinPublication::updateOrCreate(
            ['academic_class_id' => 1, 'semester_id' => $semesterId],
            ['status' => BulletinPublication::STATUS_PUBLISHED, 'validated_by' => $adminUserId, 'validated_at' => now(), 'published_at' => now()]
        );
        BulletinPublication::updateOrCreate(
            ['academic_class_id' => 2, 'semester_id' => $semesterId],
            ['status' => BulletinPublication::STATUS_PUBLISHED, 'validated_by' => $adminUserId, 'validated_at' => now(), 'published_at' => now()]
        );

        foreach ([[1, 1], [1, 2], [2, 1]] as [$classId, $subjectId]) {
            BulletinSubjectPublication::updateOrCreate(
                ['academic_class_id' => $classId, 'subject_id' => $subjectId, 'semester_id' => $semesterId],
                ['status' => BulletinSubjectPublication::STATUS_PUBLISHED, 'published_by' => $adminUserId, 'published_at' => now()]
            );
        }

        // --- Attendance: last 20 weekdays for each child ---------------------
        $this->seedAttendance($child1->id, 1, $schoolId, $branchId, $recordedByUserId, [
            // offset in weekdays back from today => ['status', late_minutes, justified]
            2 => ['late', 10, null],
            5 => ['absent', null, true],
            9 => ['absent', null, false],
            14 => ['late', 5, null],
        ]);
        $this->seedAttendance($child2->id, 2, $schoolId, $branchId, $recordedByUserId, [
            4 => ['late', 8, null],
            11 => ['absent', null, true],
        ]);

        // --- Infirmary visits --------------------------------------------------
        Intervention::firstOrCreate(
            ['student_id' => $child1->id, 'motive' => 'Fièvre', 'arrival_time' => Carbon::now()->subDays(6)->setTime(10, 45)],
            ['school_id' => $schoolId, 'temperature' => 38.2, 'care_notes' => 'Prise en charge à l\'infirmerie, repos de 30 minutes conseillé.', 'decision' => 'retour_classe', 'created_by' => $adminUserId]
        );
        Intervention::firstOrCreate(
            ['student_id' => $child1->id, 'motive' => 'Douleur abdominale', 'arrival_time' => Carbon::now()->subDays(3)->setTime(13, 15)],
            ['school_id' => $schoolId, 'temperature' => null, 'care_notes' => 'Douleurs légères, parents prévenus par précaution.', 'decision' => 'parents_appeles', 'created_by' => $adminUserId]
        );
        Intervention::firstOrCreate(
            ['student_id' => $child2->id, 'motive' => 'Blessure légère', 'arrival_time' => Carbon::now()->subDays(4)->setTime(11, 0)],
            ['school_id' => $schoolId, 'temperature' => null, 'care_notes' => 'Écorchure au genou suite à une chute en récréation, nettoyage et pansement.', 'decision' => 'retour_classe', 'created_by' => $adminUserId]
        );

        // --- Behaviour observations --------------------------------------------
        ReportCardObservation::firstOrCreate(
            ['student_id' => $child1->id, 'teacher_id' => $teacherId, 'semester_id' => $semesterId],
            ['comment' => 'Élève sérieux et volontaire, bonne intégration dans le groupe classe.']
        );
        ReportCardObservation::firstOrCreate(
            ['student_id' => $child2->id, 'teacher_id' => $teacherId, 'semester_id' => $semesterId],
            ['comment' => 'Très bon esprit d\'équipe, participe activement aux activités de classe.']
        );

        // --- School events -------------------------------------------------------
        Event::updateOrCreate(
            ['school_id' => $schoolId, 'title' => 'Réunion parents-professeurs'],
            [
                'type' => 'reunion',
                'organizer_name' => 'Direction pédagogique',
                'description' => 'Rencontre trimestrielle entre parents et équipe pédagogique.',
                'start_at' => Carbon::now()->addDays(5)->setTime(17, 0),
                'end_at' => Carbon::now()->addDays(5)->setTime(19, 0),
                'status' => 'confirmed',
                'created_by' => $adminUserId,
            ]
        );
        Event::updateOrCreate(
            ['school_id' => $schoolId, 'title' => 'Journée pédagogique — établissement fermé'],
            [
                'type' => 'fermeture',
                'organizer_name' => 'Direction',
                'description' => 'Journée de formation des enseignants, aucun cours ce jour.',
                'start_at' => Carbon::now()->addDays(12)->setTime(0, 0),
                'end_at' => Carbon::now()->addDays(12)->setTime(23, 59),
                'status' => 'confirmed',
                'created_by' => $adminUserId,
            ]
        );

        // --- Fees for child 2 (5ème) ---------------------------------------------
        $feeLevel5eme = FeeLevel::updateOrCreate(
            ['school_id' => $schoolId, 'level' => '5ème', 'academic_year' => '2025-2026'],
            ['registration_fee' => 50000, 'monthly_fee' => 38000, 'installments_count' => 9, 'start_date' => '2026-08-09']
        );

        if (Payment::where('student_id', $child2->id)->doesntExist()) {
            Payment::create([
                'school_id' => $schoolId,
                'student_id' => $child2->id,
                'amount' => $feeLevel5eme->registration_fee + $feeLevel5eme->monthly_fee * $feeLevel5eme->installments_count,
                'method' => 'bank_transfer',
                'reference' => 'DEMO-FATOU-001',
                'paid_at' => Carbon::now()->subDays(20),
                'note' => 'Paiement intégral à l\'inscription.',
                'recorded_by' => $adminUserId,
            ]);
        }

        $this->command?->info('Demo data seeded for parent 0102723408: children #'.$child1->id.' (Amadou) and #'.$child2->id.' (Fatou).');
    }

    private function grade(int $studentId, int $subjectId, int $semesterId, int $teacherId, int $evaluationTypeId, float $score, string $remark): void
    {
        BulletinGrade::updateOrCreate(
            ['student_id' => $studentId, 'subject_id' => $subjectId, 'semester_id' => $semesterId, 'evaluation_type_id' => $evaluationTypeId],
            ['teacher_id' => $teacherId, 'score' => $score, 'remark' => $remark]
        );
    }

    /** @param array<int, array{0: string, 1: ?int, 2: ?bool}> $overridesByWeekdayOffset */
    private function seedAttendance(int $studentId, int $classId, int $schoolId, int $branchId, int $recordedBy, array $overridesByWeekdayOffset): void
    {
        $date = Carbon::now();
        $weekdayOffset = 0;

        while ($weekdayOffset <= 19) {
            if ($date->isWeekend()) {
                $date->subDay();
                continue;
            }

            $override = $overridesByWeekdayOffset[$weekdayOffset] ?? null;
            [$status, $lateMinutes, $justified] = $override ?? [AttendanceRecord::STATUS_PRESENT, null, null];

            AttendanceRecord::updateOrCreate(
                ['student_id' => $studentId, 'date' => $date->toDateString()],
                [
                    'school_id' => $schoolId,
                    'branch_id' => $branchId,
                    'academic_class_id' => $classId,
                    'status' => $status,
                    'late_minutes' => $lateMinutes,
                    'justified' => $justified,
                    'recorded_by' => $recordedBy,
                ]
            );

            $date->subDay();
            $weekdayOffset++;
        }
    }
}
