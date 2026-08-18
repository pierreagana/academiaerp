<?php

namespace App\Modules\SuperAdmin\Application\Services;

use App\Modules\Academic\Domain\Models\Branch;
use App\Modules\Academic\Domain\Models\Staff;
use App\Modules\Academic\Domain\Models\Student;
use App\Modules\Academic\Domain\Models\Teacher;
use App\Modules\Canteen\Domain\Models\MenuWeek;
use App\Modules\Cards\Domain\Models\CardTemplate;
use App\Modules\Communication\Domain\Models\Event;
use App\Modules\Finance\Domain\Models\Expense;
use App\Modules\Finance\Domain\Models\Payment;
use App\Modules\Finance\Domain\Models\Scholarship;
use App\Modules\HR\Domain\Models\Contract;
use App\Modules\Infirmary\Domain\Models\Intervention;
use App\Modules\Library\Domain\Models\Book;
use App\Modules\Presence\Domain\Models\AccessLog;
use App\Modules\Presence\Domain\Models\AttendanceRecord;
use App\Modules\Bulletin\Domain\Models\BulletinGrade;
use App\Modules\ReportCard\Domain\Models\ReportCardAssessment;
use App\Modules\SuperAdmin\Domain\Models\School;
use App\Modules\Transport\Domain\Models\Bus;
use App\Modules\Transport\Domain\Models\Route as TransportRoute;

/**
 * Single source of truth for "which schools actually use this module".
 * There is no per-module subscription/activation table, so adoption is
 * measured the only honest way available: real rows of data the school has
 * created in that module.
 */
class ModuleAdoptionService
{
    public function adoptedSchoolIds(string $slug): array
    {
        return match ($slug) {
            'core-academy'        => School::pluck('id')->all(),
            'students-guardians'  => Student::distinct('school_id')->pluck('school_id')->all(),
            'staff-management'    => Teacher::distinct('school_id')->pluck('school_id')
                                        ->merge(Staff::distinct('school_id')->pluck('school_id'))
                                        ->unique()->values()->all(),
            'cards-diplomas'      => CardTemplate::distinct('school_id')->pluck('school_id')->all(),
            'presence-access'     => AttendanceRecord::distinct('school_id')->pluck('school_id')
                                        ->merge(AccessLog::distinct('school_id')->pluck('school_id'))
                                        ->unique()->values()->all(),
            'fees'                => Payment::distinct('school_id')->pluck('school_id')->all(),
            'scholarships'        => Scholarship::distinct('school_id')->pluck('school_id')->all(),
            'expenses-budgets'    => Expense::distinct('school_id')->pluck('school_id')->all(),
            'hr-payroll'          => Contract::distinct('school_id')->pluck('school_id')->all(),
            'library'             => Book::distinct('school_id')->pluck('school_id')->all(),
            'canteen'             => MenuWeek::distinct('school_id')->pluck('school_id')->all(),
            'infirmary'           => Intervention::distinct('school_id')->pluck('school_id')->all(),
            'transport'           => TransportRoute::distinct('school_id')->pluck('school_id')
                                        ->merge(Bus::distinct('school_id')->pluck('school_id'))
                                        ->unique()->values()->all(),
            'events'              => Event::distinct('school_id')->pluck('school_id')->all(),
            'multi-campus'        => Branch::select('school_id')
                                        ->groupBy('school_id')
                                        ->havingRaw('count(*) > 1')
                                        ->pluck('school_id')->all(),
            'report-card'         => ReportCardAssessment::query()
                                        ->join('students', 'students.id', '=', 'report_card_assessments.student_id')
                                        ->distinct()
                                        ->pluck('students.school_id')->all(),
            'bulletins'           => BulletinGrade::query()
                                        ->join('students', 'students.id', '=', 'bulletin_grades.student_id')
                                        ->distinct()
                                        ->pluck('students.school_id')->all(),
            default                => [],
        };
    }

    public function count(string $slug): int
    {
        return count($this->adoptedSchoolIds($slug));
    }
}
