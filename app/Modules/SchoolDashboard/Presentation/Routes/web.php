<?php

use App\Modules\SchoolDashboard\Presentation\Controllers\AcademicController;
use Illuminate\Support\Facades\Route;
use App\Modules\SchoolDashboard\Presentation\Controllers\DashboardController;
use App\Modules\SchoolDashboard\Presentation\Controllers\RoleController;
use App\Modules\SchoolDashboard\Presentation\Controllers\FeeController;
use App\Modules\SchoolDashboard\Presentation\Controllers\ScholarshipController;
use App\Modules\SchoolDashboard\Presentation\Controllers\ExpenseController;
use App\Modules\SchoolDashboard\Presentation\Controllers\EventController;
use App\Modules\SchoolDashboard\Presentation\Controllers\LibraryController;
use App\Modules\SchoolDashboard\Presentation\Controllers\CanteenController;
use App\Modules\SchoolDashboard\Presentation\Controllers\InfirmaryController;
use App\Modules\SchoolDashboard\Presentation\Controllers\TransportController;
use App\Modules\SchoolDashboard\Presentation\Controllers\HRController;
use App\Modules\SchoolDashboard\Presentation\Controllers\CardController;
use App\Modules\SchoolDashboard\Presentation\Controllers\PresenceController;
use App\Modules\SchoolDashboard\Presentation\Controllers\BranchController;
use App\Modules\SchoolDashboard\Presentation\Controllers\ExtensionController;
use App\Modules\SchoolDashboard\Presentation\Controllers\BillingController;
use App\Modules\SchoolDashboard\Presentation\Controllers\WalletController;
use App\Modules\SchoolDashboard\Presentation\Controllers\ReportCardController;
use App\Modules\SchoolDashboard\Presentation\Controllers\BulletinController;
use App\Modules\SchoolDashboard\Presentation\Controllers\TeacherPortalController;
use App\Modules\SchoolDashboard\Presentation\Controllers\HomeworkController;
use App\Modules\SchoolDashboard\Presentation\Controllers\SchoolTrackController;
use App\Modules\SchoolDashboard\Presentation\Controllers\ExamResultsController;

use App\Modules\SchoolDashboard\Presentation\Controllers\AuthController;
use App\Modules\SchoolDashboard\Presentation\Controllers\SupportController;

Route::prefix('school')->name('school.')->group(function () {
    Route::middleware(['auth', 'school'])->group(function () {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('/ai-insights', [DashboardController::class, 'aiInsights'])->name('dashboard.ai-insights');
        Route::get('/mes-etablissements', [\App\Modules\SchoolDashboard\Presentation\Controllers\FounderController::class, 'dashboard'])->name('founder.dashboard');
        Route::middleware('permission:establishment.manage')->group(function () {
            Route::get('/establishment', [DashboardController::class, 'establishment'])->name('establishment');
            Route::get('/establishment/edit', [DashboardController::class, 'editEstablishment'])->name('establishment.edit')->middleware('permission:establishment.manage,edit');
            Route::put('/establishment', [DashboardController::class, 'updateEstablishment'])->name('establishment.update')->middleware('permission:establishment.manage,update');
            Route::get('/school-track', [SchoolTrackController::class, 'show'])->name('school-track');
            Route::get('/school-track/edit', [SchoolTrackController::class, 'edit'])->name('school-track.edit');
            Route::put('/school-track', [SchoolTrackController::class, 'update'])->name('school-track.update');
        });
        Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
        Route::put('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/branches/switch', [BranchController::class, 'switch'])->name('branches.switch');

        Route::get('/support', [SupportController::class, 'index'])->name('support');
        Route::post('/support', [SupportController::class, 'store'])->name('support.store');
        Route::get('/support/{id}', [SupportController::class, 'show'])->name('support.show')->whereNumber('id');
        Route::post('/support/{id}/reply', [SupportController::class, 'reply'])->name('support.reply')->whereNumber('id');

        // Espace Enseignant (self-service, identity-gated in the controller — teacher role only, not a delegable permission)
        Route::prefix('teacher')->name('teacher.')->group(function () {
            Route::get('/classes', [TeacherPortalController::class, 'classes'])->name('classes');
            Route::get('/classes/{classId}/planning', [TeacherPortalController::class, 'classSchedule'])->name('classes.planning')->whereNumber('classId');
            Route::post('/checkin', [TeacherPortalController::class, 'checkIn'])->name('checkin');
            Route::post('/checkin-school', [TeacherPortalController::class, 'checkInSchool'])->name('checkin-school');
            Route::get('/pointages', [TeacherPortalController::class, 'attendanceHistory'])->name('attendance-history');
            Route::get('/pointages/export', [TeacherPortalController::class, 'exportAttendanceHistory'])->name('attendance-history.export');
            Route::get('/diplomes', [TeacherPortalController::class, 'diplomas'])->name('diplomas');
            Route::get('/diplomes/{id}/print', [TeacherPortalController::class, 'printDiploma'])->name('diplomas.print');
        });

        Route::middleware('permission:branches.manage')->group(function () {
            Route::get('/branches', [BranchController::class, 'index'])->name('branches');
            Route::post('/branches', [BranchController::class, 'store'])->name('branches.store')->middleware('permission:branches.manage,create');
            Route::put('/branches/{id}', [BranchController::class, 'update'])->name('branches.update')->middleware('permission:branches.manage,update');
            Route::delete('/branches/{id}', [BranchController::class, 'destroy'])->name('branches.destroy')->middleware('permission:branches.manage,delete');
            Route::post('/branches/{id}/set-main', [BranchController::class, 'setMain'])->name('branches.set-main')->middleware('permission:branches.manage,update');
        });

        // Auth Routes
        Route::get('/login/staff', [AuthController::class, 'showStaffLoginForm'])->name('login.staff');
        Route::get('/login/portal', [AuthController::class, 'showPortalLoginForm'])->name('login.portal');

        // Roles & Permissions (admin-only, checked in controller — not a delegable permission)
        Route::get('/roles', [RoleController::class, 'index'])->name('roles');
        Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
        Route::put('/roles/{id}/rename', [RoleController::class, 'rename'])->name('roles.rename');
        Route::post('/roles/{id}/permissions', [RoleController::class, 'updateMatrix'])->name('roles.permissions.update');
        Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

        // Extensions (paid add-on modules — admin-only, checked in controller)
        Route::get('/extensions', [ExtensionController::class, 'index'])->name('extensions');
        Route::post('/extensions', [ExtensionController::class, 'store'])->name('extensions.store');
        Route::get('/forfait', [ExtensionController::class, 'plans'])->name('plans');
        Route::post('/forfait', [ExtensionController::class, 'requestPlan'])->name('plans.request');

        // Billing (SaaS invoices) & Academia Pay wallet
        Route::get('/facturation', [BillingController::class, 'index'])->name('billing');
        Route::post('/facturation/{invoice}/payer/{method}', [BillingController::class, 'pay'])->name('billing.pay');
        Route::get('/facturation/succes', [BillingController::class, 'success'])->name('billing.success');
        Route::get('/facturation/annule', [BillingController::class, 'cancel'])->name('billing.cancel');
        Route::get('/portefeuille', [WalletController::class, 'index'])->name('wallet');
        Route::post('/portefeuille/recharger', [WalletController::class, 'recharge'])->name('wallet.recharge');

        // Languages
        Route::middleware('permission:academic.languages.manage')->group(function () {
            Route::get('/academic/languages', [AcademicController::class, 'languages'])->name('academic.languages');
            Route::post('/academic/languages', [AcademicController::class, 'storeLanguage'])->name('academic.languages.store')->middleware('permission:academic.languages.manage,create');
            Route::put('/academic/languages/{id}', [AcademicController::class, 'updateLanguage'])->name('academic.languages.update')->middleware('permission:academic.languages.manage,update');
            Route::delete('/academic/languages/{id}', [AcademicController::class, 'destroyLanguage'])->name('academic.languages.destroy')->middleware('permission:academic.languages.manage,delete');
        });

        // Rooms & Buildings
        Route::middleware('permission:academic.rooms.manage')->group(function () {
            Route::get('/academic/rooms', [AcademicController::class, 'rooms'])->name('academic.rooms');
            Route::post('/academic/buildings', [AcademicController::class, 'storeBuilding'])->name('academic.buildings.store')->middleware('permission:academic.rooms.manage,create');
            Route::put('/academic/buildings/{id}', [AcademicController::class, 'updateBuilding'])->name('academic.buildings.update')->middleware('permission:academic.rooms.manage,update');
            Route::delete('/academic/buildings/{id}', [AcademicController::class, 'destroyBuilding'])->name('academic.buildings.destroy')->middleware('permission:academic.rooms.manage,delete');
            Route::post('/academic/rooms', [AcademicController::class, 'storeRoom'])->name('academic.rooms.store')->middleware('permission:academic.rooms.manage,create');
            Route::put('/academic/rooms/{id}', [AcademicController::class, 'updateRoom'])->name('academic.rooms.update')->middleware('permission:academic.rooms.manage,update');
            Route::delete('/academic/rooms/{id}', [AcademicController::class, 'destroyRoom'])->name('academic.rooms.destroy')->middleware('permission:academic.rooms.manage,delete');
        });

        // Cartes
        Route::middleware('permission:academic.cards.manage')->prefix('academic/cards')->name('academic.cards.')->group(function () {
            Route::get('/{type}', [CardController::class, 'show'])->name('show')->where('type', 'student|staff');
            Route::post('/{type}', [CardController::class, 'store'])->name('store')->where('type', 'student|staff')->middleware('permission:academic.cards.manage,create');
            Route::post('/{type}/print', [CardController::class, 'printCards'])->name('print')->where('type', 'student|staff');
        });

        // Classes
        Route::middleware('permission:academic.classes.manage')->group(function () {
            Route::get('/academic/classes', [AcademicController::class, 'classes'])->name('academic.classes');
            Route::post('/academic/classes', [AcademicController::class, 'storeClass'])->name('academic.classes.store')->middleware('permission:academic.classes.manage,create');
            Route::put('/academic/classes/{id}', [AcademicController::class, 'updateClass'])->name('academic.classes.update')->middleware('permission:academic.classes.manage,update');
            Route::delete('/academic/classes/{id}', [AcademicController::class, 'destroyClass'])->name('academic.classes.destroy')->middleware('permission:academic.classes.manage,delete');
        });

        // Semesters
        Route::middleware('permission:academic.semesters.manage')->group(function () {
            Route::get('/academic/semesters', [AcademicController::class, 'semesters'])->name('academic.semesters');
            Route::post('/academic/semesters', [AcademicController::class, 'storeSemester'])->name('academic.semesters.store')->middleware('permission:academic.semesters.manage,create');
            Route::put('/academic/semesters/{id}', [AcademicController::class, 'updateSemester'])->name('academic.semesters.update')->middleware('permission:academic.semesters.manage,update');
            Route::delete('/academic/semesters/{id}', [AcademicController::class, 'destroySemester'])->name('academic.semesters.destroy')->middleware('permission:academic.semesters.manage,delete');
        });

        // Subjects
        Route::middleware('permission:academic.subjects.manage')->group(function () {
            Route::get('/academic/subjects', [AcademicController::class, 'subjects'])->name('academic.subjects');
            Route::post('/academic/subjects', [AcademicController::class, 'storeSubject'])->name('academic.subjects.store')->middleware('permission:academic.subjects.manage,create');
            Route::put('/academic/subjects/{id}', [AcademicController::class, 'updateSubject'])->name('academic.subjects.update')->middleware('permission:academic.subjects.manage,update');
            Route::delete('/academic/subjects/{id}', [AcademicController::class, 'destroySubject'])->name('academic.subjects.destroy')->middleware('permission:academic.subjects.manage,delete');
        });

        // Syllabuses & Lessons
        Route::middleware('permission:academic.syllabuses.manage')->group(function () {
            Route::get('/academic/syllabuses', [AcademicController::class, 'syllabuses'])->name('academic.syllabuses');
            Route::get('/academic/syllabuses/create', [AcademicController::class, 'createSyllabus'])->name('academic.syllabuses.create')->middleware('permission:academic.syllabuses.manage,create');
            Route::post('/academic/syllabuses', [AcademicController::class, 'storeSyllabus'])->name('academic.syllabuses.store')->middleware('permission:academic.syllabuses.manage,create');
            Route::delete('/academic/syllabuses/{id}', [AcademicController::class, 'destroySyllabus'])->name('academic.syllabuses.destroy')->middleware('permission:academic.syllabuses.manage,delete');

            Route::get('/academic/syllabuses/{syllabus}/lessons', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'index'])->name('academic.lessons.index');
            Route::get('/academic/syllabuses/{syllabus}/lessons/create', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'create'])->name('academic.lessons.create')->middleware('permission:academic.syllabuses.manage,create');
            Route::post('/academic/syllabuses/{syllabus}/lessons', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'store'])->name('academic.lessons.store')->middleware('permission:academic.syllabuses.manage,create');
            Route::get('/academic/syllabuses/{syllabus}/lessons/{lesson}/edit', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'edit'])->name('academic.lessons.edit')->middleware('permission:academic.syllabuses.manage,edit');
            Route::put('/academic/syllabuses/{syllabus}/lessons/{lesson}', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'update'])->name('academic.lessons.update')->middleware('permission:academic.syllabuses.manage,update');
            Route::delete('/academic/syllabuses/{syllabus}/lessons/{lesson}', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'destroy'])->name('academic.lessons.destroy')->middleware('permission:academic.syllabuses.manage,delete');
            Route::post('/academic/syllabuses/{syllabus}/lessons/{lesson}/sub-lesson-progress', [\App\Modules\SchoolDashboard\Presentation\Controllers\LessonController::class, 'updateSubLessonProgress'])->name('academic.lessons.sub-lesson-progress');
        });

        // Timetable
        Route::middleware('permission:academic.timetable.manage')->group(function () {
            Route::get('/academic/timetable', [AcademicController::class, 'timetable'])->name('academic.timetable');
            Route::post('/academic/timetable/ai-optimizer-check', [AcademicController::class, 'aiOptimizerCheck'])->name('academic.timetable.ai-optimizer-check');
            Route::get('/academic/timetable/create', [AcademicController::class, 'createTimetable'])->name('academic.timetable.create')->middleware('permission:academic.timetable.manage,create');
            Route::post('/academic/timetable/store', [AcademicController::class, 'storeTimetable'])->name('academic.timetable.store')->middleware('permission:academic.timetable.manage,create');
            Route::post('/academic/timetable/ai-analyze-draft', [AcademicController::class, 'aiAnalyzeTimetableDraft'])->name('academic.timetable.ai-analyze-draft')->middleware('permission:academic.timetable.manage,create');

            Route::get('/academic/timetable/breaks', [AcademicController::class, 'timetableBreaks'])->name('academic.timetable.breaks');
            Route::post('/academic/timetable/breaks', [AcademicController::class, 'storeTimetableBreak'])->name('academic.timetable.breaks.store')->middleware('permission:academic.timetable.manage,create');
            Route::put('/academic/timetable/breaks/{id}', [AcademicController::class, 'updateTimetableBreak'])->name('academic.timetable.breaks.update')->middleware('permission:academic.timetable.manage,update');
            Route::delete('/academic/timetable/breaks/{id}', [AcademicController::class, 'destroyTimetableBreak'])->name('academic.timetable.breaks.destroy')->middleware('permission:academic.timetable.manage,delete');
        });

        // Bulletins
        Route::middleware('permission:academic.bulletins.manage')->prefix('academic/bulletins')->name('academic.bulletins.')->group(function () {
            Route::get('/', [BulletinController::class, 'dashboard'])->name('dashboard');

            Route::get('/notes', [BulletinController::class, 'grades'])->name('grades');
            Route::post('/notes', [BulletinController::class, 'storeGrades'])->name('grades.store')->middleware('permission:academic.bulletins.manage,create');
            Route::delete('/notes/{id}', [BulletinController::class, 'destroyGradeEntry'])->name('grades.destroy')->middleware('permission:academic.bulletins.manage,update');
            Route::post('/notes/publier', [BulletinController::class, 'publishSubjectGrades'])->name('grades.publish')->middleware('permission:academic.bulletins.manage,update');
            Route::post('/notes/depublier', [BulletinController::class, 'unpublishSubjectGrades'])->name('grades.unpublish')->middleware('permission:academic.bulletins.manage,update');

            Route::get('/validation', [BulletinController::class, 'validation'])->name('validation');
            Route::post('/validation/valider', [BulletinController::class, 'validateClass'])->name('validation.validate')->middleware('permission:academic.bulletins.manage,update');
            Route::post('/validation/publier', [BulletinController::class, 'publishClass'])->name('validation.publish')->middleware('permission:academic.bulletins.manage,update');

            Route::get('/modele', [BulletinController::class, 'template'])->name('template');
            Route::post('/modele', [BulletinController::class, 'storeTemplate'])->name('template.store')->middleware('permission:academic.bulletins.manage,update');

            Route::get('/types-evaluation', [BulletinController::class, 'evaluationTypes'])->name('evaluation-types');
            Route::post('/types-evaluation', [BulletinController::class, 'storeEvaluationType'])->name('evaluation-types.store')->middleware('permission:academic.bulletins.manage,create');
            Route::put('/types-evaluation/{id}', [BulletinController::class, 'updateEvaluationType'])->name('evaluation-types.update')->middleware('permission:academic.bulletins.manage,update');
            Route::delete('/types-evaluation/{id}', [BulletinController::class, 'destroyEvaluationType'])->name('evaluation-types.destroy')->middleware('permission:academic.bulletins.manage,delete');

            Route::get('/eleves/{id}/print', [BulletinController::class, 'print'])->name('print');
        });

        // Devoirs & Interrogations
        Route::middleware('permission:academic.homework.manage')->prefix('academic/homework')->name('academic.homework.')->group(function () {
            Route::get('/homework', [HomeworkController::class, 'homeworkIndex'])->name('homework');
            Route::get('/homework/create', [HomeworkController::class, 'homeworkCreate'])->name('homework.create')->middleware('permission:academic.homework.manage,create');
            Route::post('/homework', [HomeworkController::class, 'storeHomework'])->name('homework.store')->middleware('permission:academic.homework.manage,create');

            Route::get('/tests', [HomeworkController::class, 'testsIndex'])->name('tests');
            Route::get('/tests/create', [HomeworkController::class, 'testsCreate'])->name('tests.create')->middleware('permission:academic.homework.manage,create');
            Route::post('/tests', [HomeworkController::class, 'storeTest'])->name('tests.store')->middleware('permission:academic.homework.manage,create');

            Route::get('/{homework}/submissions', [HomeworkController::class, 'submissions'])->name('submissions')->whereNumber('homework');
            Route::post('/{homework}/submissions', [HomeworkController::class, 'storeSubmissions'])->name('submissions.store')->middleware('permission:academic.homework.manage,update')->whereNumber('homework');
            Route::post('/{homework}/submission-mark', [HomeworkController::class, 'storeSubmissionMark'])->name('submission-mark')->middleware('permission:academic.homework.manage,update')->whereNumber('homework');

            Route::get('/{homework}/live', [HomeworkController::class, 'live'])->name('live')->whereNumber('homework');
            Route::post('/{homework}/start', [HomeworkController::class, 'start'])->name('start')->middleware('permission:academic.homework.manage,update')->whereNumber('homework');
            Route::post('/{homework}/stop', [HomeworkController::class, 'stop'])->name('stop')->middleware('permission:academic.homework.manage,update')->whereNumber('homework');
            Route::post('/{homework}/attendance', [HomeworkController::class, 'storeAttendance'])->name('attendance')->middleware('permission:academic.homework.manage,update')->whereNumber('homework');
            Route::get('/{homework}/attendance/refresh', [HomeworkController::class, 'attendanceCounts'])->name('attendance.refresh')->whereNumber('homework');
            Route::delete('/{homework}', [HomeworkController::class, 'destroy'])->name('destroy')->middleware('permission:academic.homework.manage,delete')->whereNumber('homework');
        });

        Route::middleware('permission:academic.exam-results.manage')->prefix('exam-results')->name('exam-results.')->group(function () {
            Route::get('/', [ExamResultsController::class, 'index'])->name('index');
            Route::get('/create', [ExamResultsController::class, 'create'])->name('create')->middleware('permission:academic.exam-results.manage,create');
            Route::post('/', [ExamResultsController::class, 'store'])->name('store')->middleware('permission:academic.exam-results.manage,create');
        });

        // Students
        Route::middleware('permission:academic.students.manage')->group(function () {
            Route::get('/academic/students', [AcademicController::class, 'students'])->name('academic.students');
            Route::get('/academic/students/create', [AcademicController::class, 'createStudent'])->name('academic.students.create')->middleware('permission:academic.students.manage,create');
            Route::post('/academic/students', [AcademicController::class, 'storeStudent'])->name('academic.students.store')->middleware('permission:academic.students.manage,create');
            Route::get('/academic/students/{id}/edit', [AcademicController::class, 'editStudent'])->name('academic.students.edit')->middleware('permission:academic.students.manage,edit');
            Route::put('/academic/students/{id}', [AcademicController::class, 'updateStudent'])->name('academic.students.update')->middleware('permission:academic.students.manage,update');
            Route::delete('/academic/students/{id}', [AcademicController::class, 'destroyStudent'])->name('academic.students.destroy')->middleware('permission:academic.students.manage,delete');
            Route::get('/academic/students/transfer', [AcademicController::class, 'transferStudents'])->name('academic.students.transfer')->middleware('permission:academic.students.manage,update');
            Route::post('/academic/students/transfer', [AcademicController::class, 'storeTransfer'])->name('academic.students.transfer.store')->middleware('permission:academic.students.manage,update');
            Route::get('/academic/students/promote', [AcademicController::class, 'promoteStudents'])->name('academic.students.promote')->middleware('permission:academic.students.manage,update');
            Route::post('/academic/students/promote', [AcademicController::class, 'storePromotion'])->name('academic.students.promote.store')->middleware('permission:academic.students.manage,update');
            Route::get('/academic/students/{id}', [AcademicController::class, 'showStudent'])->name('academic.students.show');
            Route::post('/academic/students/{id}/documents', [AcademicController::class, 'storeStudentDocument'])->name('academic.students.documents.store')->middleware('permission:academic.students.manage,create');
            Route::put('/academic/students/documents/{id}', [AcademicController::class, 'updateStudentDocumentStatus'])->name('academic.students.documents.status')->middleware('permission:academic.students.manage,update');
            Route::delete('/academic/students/documents/{id}', [AcademicController::class, 'destroyStudentDocument'])->name('academic.students.documents.destroy')->middleware('permission:academic.students.manage,delete');
            Route::post('/academic/students/{id}/disciplinary-records', [AcademicController::class, 'storeDisciplinaryRecord'])->name('academic.students.disciplinary.store')->middleware('permission:academic.students.manage,create');
            Route::delete('/academic/students/disciplinary-records/{id}', [AcademicController::class, 'destroyDisciplinaryRecord'])->name('academic.students.disciplinary.destroy')->middleware('permission:academic.students.manage,delete');
        });

        // Parents / Guardians
        Route::middleware('permission:academic.parents.manage')->group(function () {
            Route::get('/academic/parents', [AcademicController::class, 'parents'])->name('academic.parents');
            Route::get('/academic/parents/create', [AcademicController::class, 'createParent'])->name('academic.parents.create')->middleware('permission:academic.parents.manage,create');
            Route::post('/academic/parents', [AcademicController::class, 'storeParent'])->name('academic.parents.store')->middleware('permission:academic.parents.manage,create');
            Route::post('/academic/parents/quick-create', [AcademicController::class, 'storeParentAjax'])->name('academic.parents.quick-create')->middleware('permission:academic.parents.manage,create');
            Route::get('/academic/parents/{id}/edit', [AcademicController::class, 'editParent'])->name('academic.parents.edit')->middleware('permission:academic.parents.manage,edit');
            Route::put('/academic/parents/{id}', [AcademicController::class, 'updateParent'])->name('academic.parents.update')->middleware('permission:academic.parents.manage,update');
            Route::delete('/academic/parents/{id}', [AcademicController::class, 'destroyParent'])->name('academic.parents.destroy')->middleware('permission:academic.parents.manage,delete');
        });

        // Teachers
        Route::middleware('permission:academic.teachers.manage')->group(function () {
            Route::get('/academic/teachers', [AcademicController::class, 'teachers'])->name('academic.teachers');
            Route::get('/academic/teachers/create', [AcademicController::class, 'createTeacher'])->name('academic.teachers.create')->middleware('permission:academic.teachers.manage,create');
            Route::post('/academic/teachers', [AcademicController::class, 'storeTeacher'])->name('academic.teachers.store')->middleware('permission:academic.teachers.manage,create');
            Route::post('/academic/teachers/ai-suggest-hours', [AcademicController::class, 'aiSuggestTeacherHours'])->name('academic.teachers.ai-suggest-hours')->middleware('permission:academic.teachers.manage,create');
            Route::get('/academic/teachers/{id}', [AcademicController::class, 'showTeacher'])->name('academic.teachers.show');
            Route::get('/academic/teachers/{id}/edit', [AcademicController::class, 'editTeacher'])->name('academic.teachers.edit')->middleware('permission:academic.teachers.manage,edit');
            Route::put('/academic/teachers/{id}', [AcademicController::class, 'updateTeacher'])->name('academic.teachers.update')->middleware('permission:academic.teachers.manage,update');
            Route::delete('/academic/teachers/{id}', [AcademicController::class, 'destroyTeacher'])->name('academic.teachers.destroy')->middleware('permission:academic.teachers.manage,delete');
        });

        // Personnel
        Route::middleware('permission:academic.personnel.manage')->group(function () {
            Route::get('/academic/personnel', [AcademicController::class, 'personnel'])->name('academic.personnel');
            Route::get('/academic/personnel/create', [AcademicController::class, 'createPersonnel'])->name('academic.personnel.create')->middleware('permission:academic.personnel.manage,create');
            Route::post('/academic/personnel', [AcademicController::class, 'storePersonnel'])->name('academic.personnel.store')->middleware('permission:academic.personnel.manage,create');
            Route::get('/academic/personnel/{id}/edit', [AcademicController::class, 'editPersonnel'])->name('academic.personnel.edit')->middleware('permission:academic.personnel.manage,edit');
            Route::put('/academic/personnel/{id}', [AcademicController::class, 'updatePersonnel'])->name('academic.personnel.update')->middleware('permission:academic.personnel.manage,update');
            Route::delete('/academic/personnel/{id}', [AcademicController::class, 'destroyPersonnel'])->name('academic.personnel.destroy')->middleware('permission:academic.personnel.manage,delete');
        });

        // Récompenses & Diplômes
        Route::middleware('permission:academic.awards.manage')->prefix('academic/awards')->name('academic.awards.')->group(function () {
            Route::get('/', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardController::class, 'index'])->name('index');
            Route::get('/create', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardController::class, 'create'])->name('create')->middleware('permission:academic.awards.manage,create');
            Route::post('/', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardController::class, 'store'])->name('store')->middleware('permission:academic.awards.manage,create');
            Route::delete('/{id}', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardController::class, 'destroy'])->name('destroy')->middleware('permission:academic.awards.manage,delete');
            Route::get('/template', [\App\Modules\SchoolDashboard\Presentation\Controllers\DiplomaTemplateController::class, 'edit'])->name('template.edit');
            Route::post('/template', [\App\Modules\SchoolDashboard\Presentation\Controllers\DiplomaTemplateController::class, 'update'])->name('template.update')->middleware('permission:academic.awards.manage,update');
            Route::get('/models', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardTypeController::class, 'index'])->name('models.index');
            Route::post('/models', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardTypeController::class, 'store'])->name('models.store')->middleware('permission:academic.awards.manage,create');
            Route::delete('/models/{id}', [\App\Modules\SchoolDashboard\Presentation\Controllers\AwardTypeController::class, 'destroy'])->name('models.destroy')->middleware('permission:academic.awards.manage,delete');
            Route::get('/{id}/print', [\App\Modules\SchoolDashboard\Presentation\Controllers\DiplomaTemplateController::class, 'print'])->name('print');
        });

        // Présence & Contrôle d'Accès
        Route::middleware('permission:academic.presence.manage')->prefix('academic/presence')->name('academic.presence.')->group(function () {
            Route::get('/classe', [PresenceController::class, 'attendanceDashboard'])->name('attendance');
            Route::get('/classe/prendre', [PresenceController::class, 'takeAttendance'])->name('attendance.take');
            Route::post('/classe/prendre', [PresenceController::class, 'storeAttendance'])->name('attendance.store')->middleware('permission:academic.presence.manage,create');
            Route::get('/acces', [PresenceController::class, 'accessControlDashboard'])->name('access');
            Route::get('/acces/export', [PresenceController::class, 'exportAccessLog'])->name('access.export');
            Route::post('/acces/check-in', [PresenceController::class, 'storeCheckIn'])->name('access.checkin')->middleware('permission:academic.presence.manage,create');
            Route::post('/acces/portails', [PresenceController::class, 'storeAccessPoint'])->name('access.points.store')->middleware('permission:academic.presence.manage,create');
            Route::delete('/acces/portails/{id}', [PresenceController::class, 'destroyAccessPoint'])->name('access.points.destroy')->middleware('permission:academic.presence.manage,delete');
            Route::get('/appareils', [PresenceController::class, 'accessDevicesDashboard'])->name('access.devices');
            Route::post('/appareils', [PresenceController::class, 'storeAccessDevice'])->name('access.devices.store')->middleware('permission:academic.presence.manage,create');
            Route::post('/appareils/{id}/toggle', [PresenceController::class, 'toggleAccessDevice'])->name('access.devices.toggle')->middleware('permission:academic.presence.manage,update');
            Route::delete('/appareils/{id}', [PresenceController::class, 'destroyAccessDevice'])->name('access.devices.destroy')->middleware('permission:academic.presence.manage,delete');
        });

        // Frais Scolaires
        Route::middleware('permission:finance.fees.manage')->prefix('finance/fees')->name('finance.fees.')->group(function () {
            Route::get('/', [FeeController::class, 'overview'])->name('overview');
            Route::get('/payments', [FeeController::class, 'payments'])->name('payments');
            Route::post('/payments', [FeeController::class, 'storePayment'])->name('payments.store')->middleware('permission:finance.fees.manage,create');
            Route::get('/payments/export', [FeeController::class, 'exportPayments'])->name('payments.export');
            Route::get('/config', [FeeController::class, 'config'])->name('config');
            Route::post('/config', [FeeController::class, 'storeFeeLevel'])->name('config.store')->middleware('permission:finance.fees.manage,create');
            Route::put('/config/{id}', [FeeController::class, 'updateFeeLevel'])->name('config.update')->middleware('permission:finance.fees.manage,update');
            Route::delete('/config/{id}', [FeeController::class, 'destroyFeeLevel'])->name('config.destroy')->middleware('permission:finance.fees.manage,delete');
            Route::get('/students/{id}', [FeeController::class, 'studentShow'])->name('students.show');
            Route::get('/students/{id}/export', [FeeController::class, 'exportStudentStatement'])->name('students.export');
        });

        // Bourses
        Route::middleware('permission:finance.scholarships.manage')->prefix('finance/scholarships')->name('finance.scholarships.')->group(function () {
            Route::get('/', [ScholarshipController::class, 'dashboard'])->name('dashboard');
            Route::get('/students', [ScholarshipController::class, 'students'])->name('students');
            Route::post('/', [ScholarshipController::class, 'store'])->name('store')->middleware('permission:finance.scholarships.manage,create');
            Route::post('/{id}/approve', [ScholarshipController::class, 'approve'])->name('approve')->middleware('permission:finance.scholarships.manage,update');
            Route::post('/{id}/reject', [ScholarshipController::class, 'reject'])->name('reject')->middleware('permission:finance.scholarships.manage,update');
            Route::post('/{id}/suspend', [ScholarshipController::class, 'suspend'])->name('suspend')->middleware('permission:finance.scholarships.manage,update');
            Route::post('/{id}/reactivate', [ScholarshipController::class, 'reactivate'])->name('reactivate')->middleware('permission:finance.scholarships.manage,update');
            Route::post('/{id}/renew', [ScholarshipController::class, 'renew'])->name('renew')->middleware('permission:finance.scholarships.manage,update');

            Route::get('/criteria', [ScholarshipController::class, 'criteria'])->name('criteria');
            Route::post('/criteria', [ScholarshipController::class, 'storeType'])->name('criteria.store')->middleware('permission:finance.scholarships.manage,create');
            Route::put('/criteria/{id}', [ScholarshipController::class, 'updateType'])->name('criteria.update')->middleware('permission:finance.scholarships.manage,update');
            Route::delete('/criteria/{id}', [ScholarshipController::class, 'destroyType'])->name('criteria.destroy')->middleware('permission:finance.scholarships.manage,delete');

            Route::get('/{id}', [ScholarshipController::class, 'show'])->name('show');
            Route::get('/{id}/export', [ScholarshipController::class, 'exportCertificate'])->name('export');
            Route::post('/{id}/disbursements', [ScholarshipController::class, 'storeDisbursement'])->name('disbursements.store')->middleware('permission:finance.scholarships.manage,create');
            Route::post('/disbursements/{id}/pay', [ScholarshipController::class, 'markDisbursementPaid'])->name('disbursements.pay')->middleware('permission:finance.scholarships.manage,update');
            Route::post('/{id}/documents', [ScholarshipController::class, 'storeDocument'])->name('documents.store')->middleware('permission:finance.scholarships.manage,create');
            Route::delete('/documents/{id}', [ScholarshipController::class, 'destroyDocument'])->name('documents.destroy')->middleware('permission:finance.scholarships.manage,delete');
            Route::post('/{id}/grades', [ScholarshipController::class, 'storeGrade'])->name('grades.store')->middleware('permission:finance.scholarships.manage,create');
        });

        // Dépenses
        Route::middleware('permission:finance.expenses.manage')->prefix('finance/expenses')->name('finance.expenses.')->group(function () {
            Route::get('/', [ExpenseController::class, 'overview'])->name('overview');
            Route::post('/ai-analysis', [ExpenseController::class, 'aiExpenseAnalysis'])->name('ai-analysis');
            Route::get('/transactions', [ExpenseController::class, 'transactions'])->name('transactions');
            Route::get('/transactions/export/excel', [ExpenseController::class, 'exportExcel'])->name('transactions.export.excel');
            Route::get('/transactions/export/pdf', [ExpenseController::class, 'exportPdf'])->name('transactions.export.pdf');
            Route::get('/create', [ExpenseController::class, 'create'])->name('create')->middleware('permission:finance.expenses.manage,create');
            Route::post('/', [ExpenseController::class, 'store'])->name('store')->middleware('permission:finance.expenses.manage,create');
            Route::post('/{id}/approve', [ExpenseController::class, 'approve'])->name('approve')->middleware('permission:finance.expenses.manage,update');
            Route::post('/{id}/reject', [ExpenseController::class, 'reject'])->name('reject')->middleware('permission:finance.expenses.manage,update');
            Route::delete('/{id}', [ExpenseController::class, 'destroy'])->name('destroy')->middleware('permission:finance.expenses.manage,delete');
            Route::post('/generate-salaries', [ExpenseController::class, 'generateSalaries'])->name('generate-salaries')->middleware('permission:finance.expenses.manage,create');

            Route::get('/budgets', [ExpenseController::class, 'budgets'])->name('budgets');
            Route::post('/budgets', [ExpenseController::class, 'storeBudget'])->name('budgets.store')->middleware('permission:finance.expenses.manage,create');
            Route::delete('/budgets/{id}', [ExpenseController::class, 'destroyBudget'])->name('budgets.destroy')->middleware('permission:finance.expenses.manage,delete');
        });

        // Événements
        Route::middleware('permission:communication.events.manage')->prefix('communication/events')->name('communication.events.')->group(function () {
            Route::get('/', [EventController::class, 'dashboard'])->name('dashboard');
            Route::get('/calendar', [EventController::class, 'calendar'])->name('calendar');
            Route::get('/create', [EventController::class, 'create'])->name('create')->middleware('permission:communication.events.manage,create');
            Route::get('/ai-logistics-suggestion', [EventController::class, 'aiLogisticsSuggestion'])->name('ai-logistics-suggestion');
            Route::post('/', [EventController::class, 'store'])->name('store')->middleware('permission:communication.events.manage,create');
            Route::get('/{id}/edit', [EventController::class, 'edit'])->name('edit')->middleware('permission:communication.events.manage,edit');
            Route::put('/{id}', [EventController::class, 'update'])->name('update')->middleware('permission:communication.events.manage,update');
            Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy')->middleware('permission:communication.events.manage,delete');
            Route::get('/{id}/export', [EventController::class, 'exportAttendance'])->name('export');
            Route::post('/{eventId}/registrations/{studentId}/authorization', [EventController::class, 'updateAuthorization'])->name('registrations.authorization')->middleware('permission:communication.events.manage,update');
            Route::post('/{eventId}/registrations/{studentId}/payment', [EventController::class, 'updatePayment'])->name('registrations.payment')->middleware('permission:communication.events.manage,update');
            Route::get('/{id}', [EventController::class, 'show'])->name('show');
        });

        // Bibliothèque
        Route::middleware('permission:library.manage')->prefix('library')->name('library.')->group(function () {
            Route::get('/', [LibraryController::class, 'dashboard'])->name('dashboard');
            Route::post('/overdue/remind', [LibraryController::class, 'remindOverdue'])->name('overdue.remind')->middleware('permission:library.manage,update');

            Route::get('/catalog', [LibraryController::class, 'catalog'])->name('catalog');
            Route::post('/catalog', [LibraryController::class, 'storeBook'])->name('catalog.store')->middleware('permission:library.manage,create');
            Route::put('/catalog/{id}', [LibraryController::class, 'updateBook'])->name('catalog.update')->middleware('permission:library.manage,update');
            Route::delete('/catalog/{id}', [LibraryController::class, 'destroyBook'])->name('catalog.destroy')->middleware('permission:library.manage,delete');

            Route::get('/circulation', [LibraryController::class, 'circulation'])->name('circulation');
            Route::post('/circulation/loans', [LibraryController::class, 'storeLoan'])->name('circulation.loans.store')->middleware('permission:library.manage,create');
            Route::post('/circulation/loans/{id}/return', [LibraryController::class, 'returnLoan'])->name('circulation.loans.return')->middleware('permission:library.manage,update');
            Route::post('/circulation/loans/{id}/remind', [LibraryController::class, 'remindLoan'])->name('circulation.loans.remind')->middleware('permission:library.manage,update');
            Route::post('/circulation/quick-return', [LibraryController::class, 'quickReturn'])->name('circulation.quick-return')->middleware('permission:library.manage,update');

            Route::get('/settings', [LibraryController::class, 'settings'])->name('settings');
            Route::post('/settings/rules', [LibraryController::class, 'updateRules'])->name('settings.rules.update')->middleware('permission:library.manage,update');
            Route::post('/settings/categories', [LibraryController::class, 'storeCategory'])->name('settings.categories.store')->middleware('permission:library.manage,create');
            Route::delete('/settings/categories/{id}', [LibraryController::class, 'destroyCategory'])->name('settings.categories.destroy')->middleware('permission:library.manage,delete');
            Route::post('/settings/access', [LibraryController::class, 'updateAccessSettings'])->name('settings.access.update')->middleware('permission:library.manage,update');
        });

        // Cantine
        Route::middleware('permission:canteen.manage')->prefix('canteen')->name('canteen.')->group(function () {
            Route::get('/', [CanteenController::class, 'dashboard'])->name('dashboard');
            Route::post('/ai-insight', [CanteenController::class, 'aiCanteenInsight'])->name('ai-insight');

            Route::get('/planning', [CanteenController::class, 'planning'])->name('planning');
            Route::post('/planning/ai-advice', [CanteenController::class, 'aiPlanningAdvice'])->name('planning.ai-advice');
            Route::post('/planning/items', [CanteenController::class, 'storeMenuItem'])->name('planning.items.store')->middleware('permission:canteen.manage,create');
            Route::delete('/planning/items/{id}', [CanteenController::class, 'destroyMenuItem'])->name('planning.items.destroy')->middleware('permission:canteen.manage,delete');
            Route::post('/planning/publish', [CanteenController::class, 'publishWeek'])->name('planning.publish')->middleware('permission:canteen.manage,update');
            Route::get('/planning/print', [CanteenController::class, 'printRecipeCards'])->name('planning.print');
            Route::post('/planning/tags', [CanteenController::class, 'storeTag'])->name('planning.tags.store')->middleware('permission:canteen.manage,create');
            Route::delete('/planning/tags/{id}', [CanteenController::class, 'destroyTag'])->name('planning.tags.destroy')->middleware('permission:canteen.manage,delete');
            Route::post('/planning/allergens', [CanteenController::class, 'storeAllergen'])->name('planning.allergens.store')->middleware('permission:canteen.manage,create');
            Route::delete('/planning/allergens/{id}', [CanteenController::class, 'destroyAllergen'])->name('planning.allergens.destroy')->middleware('permission:canteen.manage,delete');

            Route::get('/inventory', [CanteenController::class, 'inventory'])->name('inventory');
            Route::get('/inventory/export', [CanteenController::class, 'exportInventory'])->name('inventory.export');
            Route::post('/inventory/products', [CanteenController::class, 'storeProduct'])->name('inventory.products.store')->middleware('permission:canteen.manage,create');
            Route::post('/inventory/adjust', [CanteenController::class, 'adjustStock'])->name('inventory.adjust')->middleware('permission:canteen.manage,update');

            Route::get('/reservations', [CanteenController::class, 'reservations'])->name('reservations');
            Route::post('/reservations/ai-forecast', [CanteenController::class, 'aiReservationForecast'])->name('reservations.ai-forecast');
            Route::get('/reservations/export', [CanteenController::class, 'exportRoster'])->name('reservations.export');
            Route::post('/reservations/meals', [CanteenController::class, 'recordMeal'])->name('reservations.meals.store')->middleware('permission:canteen.manage,create');
            Route::post('/reservations/credit', [CanteenController::class, 'creditAccount'])->name('reservations.credit')->middleware('permission:canteen.manage,update');

            Route::get('/requests', [CanteenController::class, 'enrollmentRequests'])->name('requests');
            Route::post('/requests/direct-enroll', [CanteenController::class, 'directEnroll'])->name('requests.direct-enroll')->middleware('permission:canteen.manage,create');
            Route::post('/requests/{id}/approve', [CanteenController::class, 'approveEnrollment'])->name('requests.approve')->middleware('permission:canteen.manage,update');
            Route::post('/requests/{id}/reject', [CanteenController::class, 'rejectEnrollment'])->name('requests.reject')->middleware('permission:canteen.manage,update');
            Route::post('/requests/{id}/withdraw', [CanteenController::class, 'withdrawEnrollment'])->name('requests.withdraw')->middleware('permission:canteen.manage,delete');

            Route::get('/scanner', [CanteenController::class, 'scanner'])->name('scanner');
            Route::post('/scanner', [CanteenController::class, 'scan'])->name('scanner.scan')->middleware('permission:canteen.manage,create');
        });

        // Infirmerie
        Route::middleware('permission:infirmary.manage')->prefix('infirmary')->name('infirmary.')->group(function () {
            Route::get('/', [InfirmaryController::class, 'dashboard'])->name('dashboard');

            Route::get('/interventions', [InfirmaryController::class, 'interventions'])->name('interventions');
            Route::post('/interventions', [InfirmaryController::class, 'storeIntervention'])->name('interventions.store')->middleware('permission:infirmary.manage,create');
            Route::post('/interventions/motives', [InfirmaryController::class, 'storeMotive'])->name('interventions.motives.store')->middleware('permission:infirmary.manage,create');
            Route::delete('/interventions/motives/{id}', [InfirmaryController::class, 'destroyMotive'])->name('interventions.motives.destroy')->middleware('permission:infirmary.manage,delete');

            Route::get('/students', [InfirmaryController::class, 'students'])->name('students');
            Route::get('/students/{id}/print', [InfirmaryController::class, 'printHealthRecord'])->name('students.print');

            Route::get('/pharmacy', [InfirmaryController::class, 'pharmacy'])->name('pharmacy');
            Route::post('/pharmacy/medications', [InfirmaryController::class, 'storeMedication'])->name('pharmacy.medications.store')->middleware('permission:infirmary.manage,create');
            Route::post('/pharmacy/adjust', [InfirmaryController::class, 'adjustMedicationStock'])->name('pharmacy.adjust')->middleware('permission:infirmary.manage,update');
        });

        // Transport
        Route::middleware('permission:transport.manage')->prefix('transport')->name('transport.')->group(function () {
            Route::get('/', [TransportController::class, 'fleet'])->name('fleet');
            Route::post('/buses', [TransportController::class, 'storeBus'])->name('buses.store')->middleware('permission:transport.manage,create');
            Route::put('/buses/{id}', [TransportController::class, 'updateBus'])->name('buses.update')->middleware('permission:transport.manage,update');

            Route::get('/routes', [TransportController::class, 'routes'])->name('routes');
            Route::post('/routes', [TransportController::class, 'storeRoute'])->name('routes.store')->middleware('permission:transport.manage,create');
            Route::put('/routes/{id}', [TransportController::class, 'updateRoute'])->name('routes.update')->middleware('permission:transport.manage,update');

            Route::get('/stops', [TransportController::class, 'stops'])->name('stops');
            Route::post('/stops', [TransportController::class, 'storeStop'])->name('stops.store')->middleware('permission:transport.manage,create');
            Route::post('/stops/{id}/move', [TransportController::class, 'moveStop'])->name('stops.move')->middleware('permission:transport.manage,update');
            Route::delete('/stops/{id}', [TransportController::class, 'destroyStop'])->name('stops.destroy')->middleware('permission:transport.manage,delete');
            Route::post('/stops/{id}/students', [TransportController::class, 'assignStudents'])->name('stops.students.store')->middleware('permission:transport.manage,create');
            Route::delete('/stops/{id}/students/{studentId}', [TransportController::class, 'unassignStudent'])->name('stops.students.destroy')->middleware('permission:transport.manage,delete');

            Route::get('/trips', [TransportController::class, 'trips'])->name('trips');
            Route::post('/trips/ai-analysis', [TransportController::class, 'aiTripAnalysis'])->name('trips.ai-analysis');
            Route::post('/trips', [TransportController::class, 'storeTrip'])->name('trips.store')->middleware('permission:transport.manage,create');
            Route::get('/trips/export', [TransportController::class, 'exportTrips'])->name('trips.export');

            Route::get('/map', [TransportController::class, 'map'])->name('map');
            Route::get('/buses/{id}/positions', [TransportController::class, 'busPositionHistory'])->name('buses.positions');

            Route::get('/drivers', [TransportController::class, 'drivers'])->name('drivers');
            Route::post('/drivers', [TransportController::class, 'storeDriver'])->name('drivers.store')->middleware('permission:transport.manage,create');

            Route::get('/requests', [TransportController::class, 'enrollmentRequests'])->name('requests');
            Route::post('/requests/{id}/approve', [TransportController::class, 'approveEnrollment'])->name('requests.approve')->middleware('permission:transport.manage,update');
            Route::post('/requests/{id}/reject', [TransportController::class, 'rejectEnrollment'])->name('requests.reject')->middleware('permission:transport.manage,update');
            Route::post('/requests/{id}/withdraw', [TransportController::class, 'withdrawEnrollment'])->name('requests.withdraw')->middleware('permission:transport.manage,delete');

            Route::get('/scanner', [TransportController::class, 'scanner'])->name('scanner');
            Route::post('/scanner', [TransportController::class, 'scan'])->name('scanner.scan')->middleware('permission:transport.manage,create');

            Route::get('/history', [TransportController::class, 'boardingHistory'])->name('history');
        });

        // RH
        Route::middleware('permission:hr.manage')->prefix('rh')->name('hr.')->group(function () {
            Route::get('/', [HRController::class, 'dashboard'])->name('dashboard');
            Route::get('/annuaire', [HRController::class, 'directory'])->name('directory');
            Route::get('/paie', [HRController::class, 'payroll'])->name('payroll');
            Route::post('/paie/lancer', [HRController::class, 'runPayroll'])->name('payroll.run')->middleware('permission:hr.manage,create');
            Route::get('/configuration', [HRController::class, 'configuration'])->name('configuration');
            Route::post('/configuration/echelons', [HRController::class, 'storeSalaryGrade'])->name('configuration.grades.store')->middleware('permission:hr.manage,create');
            Route::post('/configuration/rubriques', [HRController::class, 'storeComponent'])->name('configuration.components.store')->middleware('permission:hr.manage,create');
            Route::post('/configuration/rubriques/{id}/toggle', [HRController::class, 'toggleComponent'])->name('configuration.components.toggle')->middleware('permission:hr.manage,update');

            Route::get('/contrats', [HRController::class, 'contracts'])->name('contracts');
            Route::post('/contrats', [HRController::class, 'storeContract'])->name('contracts.store')->middleware('permission:hr.manage,create');
            Route::post('/contrats/{id}/rappel', [HRController::class, 'acknowledgeReminder'])->name('contracts.acknowledge')->middleware('permission:hr.manage,update');
            Route::post('/contrats/types', [HRController::class, 'storeContractType'])->name('contracts.types.store')->middleware('permission:hr.manage,create');
            Route::delete('/contrats/types/{id}', [HRController::class, 'destroyContractType'])->name('contracts.types.destroy')->middleware('permission:hr.manage,delete');
        });

        // Livret Scolaire
        Route::middleware('permission:report-card.manage')->prefix('report-card')->name('report-card.')->group(function () {
            Route::get('/', [ReportCardController::class, 'dashboard'])->name('dashboard');
            Route::get('/print', [ReportCardController::class, 'printGlobalReport'])->name('print-global');

            Route::get('/referentiels', [ReportCardController::class, 'referentials'])->name('referentials');
            Route::post('/referentiels/domaines', [ReportCardController::class, 'storeDomain'])->name('domains.store')->middleware('permission:report-card.manage,create');
            Route::delete('/referentiels/domaines/{id}', [ReportCardController::class, 'destroyDomain'])->name('domains.destroy')->middleware('permission:report-card.manage,delete');
            Route::post('/referentiels/sous-domaines', [ReportCardController::class, 'storeSubdomain'])->name('subdomains.store')->middleware('permission:report-card.manage,create');
            Route::delete('/referentiels/sous-domaines/{id}', [ReportCardController::class, 'destroySubdomain'])->name('subdomains.destroy')->middleware('permission:report-card.manage,delete');
            Route::post('/referentiels/competences', [ReportCardController::class, 'storeCompetency'])->name('competencies.store')->middleware('permission:report-card.manage,create');
            Route::delete('/referentiels/competences/{id}', [ReportCardController::class, 'destroyCompetency'])->name('competencies.destroy')->middleware('permission:report-card.manage,delete');

            Route::get('/evaluation', [ReportCardController::class, 'evaluation'])->name('evaluation');
            Route::post('/evaluation', [ReportCardController::class, 'storeAssessments'])->name('evaluation.store')->middleware('permission:report-card.manage,create');

            Route::get('/eleves/{id}', [ReportCardController::class, 'studentProfile'])->name('student');
            Route::get('/eleves/{id}/print', [ReportCardController::class, 'printLivret'])->name('student.print');
            Route::post('/observations', [ReportCardController::class, 'storeObservation'])->name('observations.store')->middleware('permission:report-card.manage,create');
        });
    });
});
