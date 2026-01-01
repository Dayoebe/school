<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

// Livewire Components
use App\Livewire\{
    StudentDashboard,
    TeacherDashboard,
    ManageAcademicYears,
    ShowAcademicYear,
    ManageSemesters,
    Subjects\ManageSubjects,
    Subjects\SubjectDetail,
    Subjects\AssignTeacher,
    Teachers\ManageTeachers,
    Teachers\TeacherDetail
};
use App\Livewire\Fees\{
    ManageFeeCategories,
    ManageFees,
    ManageFeeInvoices,
    FeeInvoiceDetail
};
use App\Livewire\Sections\{ManageSections, SectionDetail};
use App\Livewire\Students\{ManageStudents, StudentDetail, PromoteStudents, GraduateStudents};
use App\Livewire\Classes\{ManageClassGroups, ManageClasses};

// Controllers 
use App\Http\Controllers\{
    PageController,
    AuthController,
    DashboardController,
    ProfileController,
    SchoolController,
    MyClassController,
    NoticeController,
    ResultController,
    AccountApplicationController,
    SyllabusController,
    TimetableController,
    CustomTimetableItemController,
    TimetableTimeSlotController,
    ExamController,
    ExamRecordController,
    ExamSlotController,
    GradeSystemController,
    AdminController,
    ParentController,
    LockUserAccountController
};

/*
|--------------------------------------------------------------------------
| MIDDLEWARE GROUPS
|--------------------------------------------------------------------------
*/

$dashboardMiddleware = [
    'auth:sanctum',
    'verified',
    'App\Http\Middleware\PreventLockAccountAccess',
    'App\Http\Middleware\EnsureDefaultPasswordIsChanged',
    'App\Http\Middleware\PreventGraduatedStudent',
    'App\Http\Middleware\EnsureSuperAdminHasSchoolId'
];

$adminMiddleware = [
    'auth:sanctum',
    'verified',
    'App\Http\Middleware\PreventLockAccountAccess',
    'App\Http\Middleware\EnsureDefaultPasswordIsChanged',
    'App\Http\Middleware\PreventGraduatedStudent'
];

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/admission', [PageController::class, 'admission'])->name('admission');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');

/*
|--------------------------------------------------------------------------
| AUTHENTICATION ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);

    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);

    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    Route::post('change-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('password.change');

    /*
    |--------------------------------------------------------------------------
    | TEACHER ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/teachers', ManageTeachers::class)->name('teachers.index');
    Route::get('/teachers/create', ManageTeachers::class)->name('teachers.create');
    Route::get('/teachers/{teacher}/edit', ManageTeachers::class)->name('teachers.edit');
    Route::get('/teachers/{teacherId}', TeacherDetail::class)->name('teachers.show');

    /*
    |--------------------------------------------------------------------------
    | SUBJECT ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/subjects', ManageSubjects::class)->name('subjects.index');
    Route::get('/subjects/create', ManageSubjects::class)->name('subjects.create');
    Route::get('/subjects/{subject}/edit', ManageSubjects::class)->name('subjects.edit');
    Route::get('/subjects/{subjectId}', SubjectDetail::class)->name('subjects.show');
    Route::get('/subjects/teacher/assign', \App\Livewire\Subjects\AssignTeacher::class)
        ->name('subjects.assign-teacher');
    // Route::get('/subjects/assign-teacher', AssignTeacher::class)->name('subjects.assign-teacher');
});

/*
|--------------------------------------------------------------------------
| FEE MANAGEMENT (Full Livewire)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->prefix('fees')->group(function () {

    // Fee Categories
    Route::get('/fee-categories', ManageFeeCategories::class)
        ->name('fee-categories.index');
    Route::get('/fee-categories/create', ManageFeeCategories::class)
        ->name('fee-categories.create');
    Route::get('/fee-categories/{feeCategory}/edit', ManageFeeCategories::class)
        ->name('fee-categories.edit');

    // Fees
    Route::get('/', ManageFees::class)
        ->name('fees.index');
    Route::get('/create', ManageFees::class)
        ->name('fees.create');
    Route::get('/{fee}/edit', ManageFees::class)
        ->name('fees.edit');

    // Fee Invoices
    Route::get('/fee-invoices', ManageFeeInvoices::class)
        ->name('fee-invoices.index');
    Route::get('/fee-invoices/create', ManageFeeInvoices::class)
        ->name('fee-invoices.create');
    Route::get('/fee-invoices/{feeInvoice}/edit', ManageFeeInvoices::class)
        ->name('fee-invoices.edit');
    Route::get('/fee-invoices/{feeInvoiceId}', FeeInvoiceDetail::class)
        ->name('fee-invoices.show');

    // Print invoice (keeping this as controller since it generates PDF)
    Route::get('/fee-invoices/{fee_invoice}/print', [FeeInvoiceController::class, 'print'])
        ->name('fee-invoices.print');
});

/*
|--------------------------------------------------------------------------
| ROLE-SPECIFIC DASHBOARDS
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/student/dashboard', StudentDashboard::class)->name('student.dashboard');
    Route::get('/teacher/dashboard', TeacherDashboard::class)->name('teacher.dashboard');
});

Route::prefix('teacher')->middleware(['auth', 'verified', 'role:teacher'])->group(function () {
    Route::resource('results', ResultController::class)->except(['show']);
});

/*
|--------------------------------------------------------------------------
| STUDENT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/students', ManageStudents::class)->name('students.index');
    Route::get('/students/create', ManageStudents::class)->name('students.create');
    Route::get('/students/{studentId}/edit', ManageStudents::class)->name('students.edit');
    Route::get('/students/{studentId}', StudentDetail::class)->name('students.show');

    // Graduation routes
    Route::get('/students/graduations/manage', GraduateStudents::class)
        ->name('students.graduate')
        ->can('read student');

    Route::get('/students/graduations/history', GraduateStudents::class)
        ->name('students.graduations')
        ->can('read student');

    // Promotion routes
    Route::get('/students/promotions/manage', PromoteStudents::class)
        ->name('students.promote')
        ->can('promote student');
});

/*
|--------------------------------------------------------------------------
| SECTION MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/sections', ManageSections::class)->name('sections.index');
    Route::get('/sections/{sectionId}', SectionDetail::class)->name('sections.show');
});

/*
|--------------------------------------------------------------------------
| RESULT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::prefix('results')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', \App\Livewire\Result\Index::class)->name('result')->can('upload result');

    Route::get('/upload/individual', \App\Livewire\Result\Upload\IndividualUpload::class)
        ->name('result.upload.individual')->can('upload result');
    Route::get('/upload/bulk', \App\Livewire\Result\Upload\BulkUpload::class)
        ->name('result.upload.bulk')->can('upload result');

    Route::get('/view/class', \App\Livewire\Result\View\ClassResults::class)
        ->name('result.view.class')->can('view result');
    Route::get('/view/subject', \App\Livewire\Result\View\SubjectResults::class)
        ->name('result.view.subject')->can('view result');
    Route::get('/view/student', \App\Livewire\Result\View\StudentResults::class)
        ->name('result.view.student')->can('view result');

    Route::get('/history', \App\Livewire\Result\StudentHistory::class)
        ->name('result.history')->can('view result');
    Route::get('/annual', [ResultController::class, 'annualClassResult'])
        ->name('result.annual')->can('view result');
    Route::get('/annual/export', [ResultController::class, 'exportAnnualResult'])
        ->name('result.annual.export')->can('view result');
    Route::get('/annual/export/pdf', [ResultController::class, 'exportAnnualPdf'])
        ->name('result.annual.export.pdf')->can('view result');
    Route::get('/annual/student/{studentId}/{academicYearId}', [ResultController::class, 'showStudentAnnualResult'])
        ->name('result.student.annual')->can('view result');

    Route::get('/print/{student}', [ResultController::class, 'print'])
        ->name('result.print')->can('view result');
    Route::get('/print-class/{academicYearId}/{semesterId}/{classId}', [ResultController::class, 'printClassResults'])
        ->name('result.print-class')->can('view result');
});

/*
|--------------------------------------------------------------------------
| DASHBOARD ADMINISTRATIVE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware($dashboardMiddleware)->prefix('dashboard')->group(function () {

    // Academic Year Management
    Route::get('academic-years', ManageAcademicYears::class)
        ->name('academic-years.index')
        ->can('viewAny', 'App\Models\AcademicYear');

    Route::get('academic-years/{academicYear}', ShowAcademicYear::class)
        ->name('academic-years.show')
        ->can('view', 'academicYear');

    // Semester Management
    Route::middleware('App\Http\Middleware\EnsureAcademicYearIsSet')->group(function () {
        Route::get('semesters', ManageSemesters::class)
            ->name('semesters.index')
            ->can('viewAny', 'App\Models\Semester');
    });

    // Class Groups & Classes
    Route::get('class-groups', ManageClassGroups::class)->name('class-groups.index');
    Route::get('classes', ManageClasses::class)->name('classes.index');
    Route::post('classes/{class}/assign-subjects', [MyClassController::class, 'assignSubjects']);
});

/*
|--------------------------------------------------------------------------
| SCHOOL ADMINISTRATION ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware($adminMiddleware)->prefix('dashboard')->group(function () {

    // School Settings
    Route::get('schools/settings', [SchoolController::class, 'settings'])
        ->name('schools.settings')
        ->middleware('App\Http\Middleware\EnsureSuperAdminHasSchoolId');

    Route::resource('schools', SchoolController::class);
    Route::post('schools/set-school', [SchoolController::class, 'setSchool'])->name('schools.setSchool');

    Route::middleware('App\Http\Middleware\EnsureSuperAdminHasSchoolId')->group(function () {

        // User Management
        Route::resource('admins', AdminController::class);
        Route::resource('parents', ParentController::class);

        Route::get('parents/{parent}/assign-student-to-parent', [ParentController::class, 'assignStudentsView'])
            ->name('parents.assign-student');
        Route::post('parents/{parent}/assign-student-to-parent', [ParentController::class, 'assignStudent']);
        Route::post('users/lock-account/{user}', LockUserAccountController::class)->name('user.lock-account');

        // Notices
        Route::resource('notices', NoticeController::class);

        // Academic Year Dependent Routes
        Route::middleware([
            'App\Http\Middleware\EnsureAcademicYearIsSet',
            'App\Http\Middleware\CreateCurrentAcademicYearRecord'
        ])->group(function () {

            // Account Applications
            Route::get('account-applications/rejected-applications', [AccountApplicationController::class, 'rejectedApplicationsView'])
                ->name('account-applications.rejected-applications');
            Route::resource('account-applications', AccountApplicationController::class)
                ->parameters(['account-applications' => 'applicant']);
            Route::get('account-applications/change-status/{applicant}', [AccountApplicationController::class, 'changeStatusView'])
                ->name('account-applications.change-status');
            Route::post('account-applications/change-status/{applicant}', [AccountApplicationController::class, 'changeStatus']);

            // Semester Dependent Routes
            Route::middleware('App\Http\Middleware\EnsureSemesterIsSet')->group(function () {


                // Syllabus
                Route::resource('syllabi', SyllabusController::class);

                // Timetables
                Route::resource('timetables', TimetableController::class);
                Route::resource('custom-timetable-items', CustomTimetableItemController::class);
                Route::get('timetables/{timetable}/manage', [TimetableController::class, 'manage'])
                    ->name('timetables.manage');
                Route::get('timetables/{timetable}/print', [TimetableController::class, 'print'])
                    ->name('timetables.print');

                Route::resource('timetables/manage/time-slots', TimetableTimeSlotController::class);
                Route::post('timetables/manage/time-slots/{time_slot}/record/create', [TimetableTimeSlotController::class, 'addTimetableRecord'])
                    ->name('timetables.records.create')->scopeBindings();

                // Exams
                Route::resource('exams', ExamController::class);
                Route::post('exams/{exam}/set-active-status', [ExamController::class, 'setExamActiveStatus'])
                    ->name('exams.set-active-status');
                Route::post('exams/{exam}/set-publish-result-status', [ExamController::class, 'setPublishResultStatus'])
                    ->name('exams.set-publish-result-status');

                Route::resource('exams/exam-records', ExamRecordController::class);
                Route::scopeBindings()->group(function () {
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class);
                });

                // Exam Reports
                Route::get('exams/tabulation-sheet', [ExamController::class, 'examTabulation'])
                    ->name('exams.tabulation');
                Route::get('exams/semester-result-tabulation', [ExamController::class, 'semesterResultTabulation'])
                    ->name('exams.semester-result-tabulation');
                Route::get('exams/academic-year-result-tabulation', [ExamController::class, 'academicYearResultTabulation'])
                    ->name('exams.academic-year-result-tabulation');
                Route::get('exams/result-checker', [ExamController::class, 'resultChecker'])
                    ->name('exams.result-checker');

                // Grade Systems
                Route::resource('grade-systems', GradeSystemController::class);
            });
        });
    });
});

/*
|--------------------------------------------------------------------------
| UTILITY & MAINTENANCE ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('artisan-commands')->group(function () {
    Route::get('/clean-invalid-results', function () {
        Artisan::call('app:clean-invalid-results');
        return "Command executed: <pre>" . Artisan::output() . "</pre>";
    })->name('artisan.clean-invalid-results');

    Route::get('/cleanup-deleted-students', function () {
        Artisan::call('cleanup:deleted-students', ['--force' => true]);
        return "Command executed: <pre>" . Artisan::output() . "</pre>";
    })->name('artisan.cleanup-deleted-students');

    Route::get('/password-default', function () {
        Artisan::call('password:default');
        return "Command executed: <pre>" . Artisan::output() . "</pre>";
    })->name('artisan.password-default');
});

Route::get('/export-db', function () {
    $file = storage_path('app/backup.sql');
    $dump = new \Ifsnop\Mysqldump\Mysqldump(
        'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE'),
        env('DB_USERNAME'),
        env('DB_PASSWORD')
    );
    $dump->start($file);
    return response()->download($file);
});

Route::get('/artisan-scheduler', function () {
    if (request('key') === env('SCHEDULER_KEY')) {
        Artisan::call('schedule:run');
        return response()->json(['status' => 'success'], 200);
    }
    abort(403, 'Unauthorized');
})->middleware('throttle:3,1');