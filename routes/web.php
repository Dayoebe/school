<?php

use Illuminate\Support\Facades\Route;

// Livewire Components
use App\Livewire\{
    Subjects\ManageSubjects,
    Subjects\SubjectDetail,
    Subjects\AssignTeacher,
    Teachers\ManageTeachers,
    Teachers\TeacherDetail
};
use App\Livewire\AcademicYears\{
    ManageAcademicYears,
    ShowAcademicYear,
    ManageSemesters
};
use App\Livewire\Cbt\{
    CbtExamSelection,
    CbtExamInterface,
    CbtViewer,
    CbtManagement
};
use App\Livewire\Fees\{
    ManageFeeCategories,
    ManageFees,
    ManageFeeInvoices,
    FeeInvoiceDetail
};

use App\Livewire\AccountApplications\ManageAccountApplications;
use App\Livewire\Sections\{ManageSections, SectionDetail};
use App\Livewire\Students\{ManageStudents, StudentDetail, PromoteStudents, GraduateStudents};
use App\Livewire\Classes\{ManageClassGroups, ManageClasses};
use App\Livewire\Admissions\ManageAdmissionRegistrations;
use App\Livewire\Contacts\ManageContactMessages;
use App\Livewire\Gallery\ManageGallery;
use App\Livewire\Syllabi\ManageSyllabi;

// Controllers 
use App\Http\Controllers\{
    PageController,
    AuthController,
    DashboardController,
    DatabaseBackupController,
    ProfileController,
    FeeInvoiceController,
    MyClassController,
    NoticeController,
    ResultController,
    ExamController,
    ExamRecordController,
    ExamSlotController,
    GradeSystemController,
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
    Route::get('reset-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});




// Account Applications (Full Livewire)
Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId', 'permission:read applicant'])
    ->group(function () {
        Route::get('account-applications', ManageAccountApplications::class)
            ->name('account-applications.index')
            ->can('viewAny', [App\Models\User::class, 'applicant']);

        Route::get('account-applications/rejected', ManageAccountApplications::class)
            ->name('account-applications.rejected-applications')
            ->can('viewAny', [App\Models\User::class, 'applicant']);

        Route::get('account-applications/{applicant}', ManageAccountApplications::class)
            ->name('account-applications.show')
            ->can('view', 'applicant');

        Route::get('account-applications/{applicant}/change-status', ManageAccountApplications::class)
            ->name('account-applications.change-status')
            ->middleware('permission:update applicant')
            ->can('update', 'applicant');
    });
/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
    Route::match(['get', 'post'], '/database/download', [DatabaseBackupController::class, 'download'])
        ->middleware('role:super-admin|super_admin')
        ->name('database.download');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('permission:view dashboard')
        ->name('dashboard');

    Route::get('/dashboard/analytics', \App\Livewire\Dashboard\AnalyticsDashboard::class)
        ->middleware('permission:read analytics dashboard')
        ->name('analytics.index');

    Route::get('/dashboard/portal-notices', \App\Livewire\Broadcasts\MyBroadcastInbox::class)
        ->middleware('permission:view own broadcasts')
        ->name('broadcasts.inbox');

    Route::get('/dashboard/parent/student-welfare', \App\Livewire\Attendance\ParentStudentWelfare::class)
        ->middleware('permission:read own child attendance|read own child discipline')
        ->name('parent.student-welfare');

    // Profile Management
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->middleware('permission:manage own profile')
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->middleware('permission:manage own profile')
        ->name('profile.update');
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])
        ->middleware('permission:change own password')
        ->name('password.change');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])
        ->middleware('permission:change own password')
        ->name('password.change.update');

    /*
    |--------------------------------------------------------------------------
    | TEACHER ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/teachers', ManageTeachers::class)
        ->middleware('permission:read teacher')
        ->name('teachers.index');
    Route::get('/teachers/create', ManageTeachers::class)
        ->middleware('permission:create teacher')
        ->name('teachers.create');
    Route::get('/teachers/{teacher}/edit', ManageTeachers::class)
        ->middleware('permission:update teacher')
        ->name('teachers.edit');
    Route::get('/teachers/{teacherId}', TeacherDetail::class)
        ->middleware('permission:read teacher')
        ->name('teachers.show');

    /*
    |--------------------------------------------------------------------------
    | SUBJECT ROUTES
    |--------------------------------------------------------------------------
    */

    Route::get('/subjects', ManageSubjects::class)
        ->middleware('permission:read subject')
        ->name('subjects.index');
    Route::get('/subjects/create', ManageSubjects::class)
        ->middleware('permission:create subject')
        ->name('subjects.create');
    Route::get('/subjects/{subject}/edit', ManageSubjects::class)
        ->middleware('permission:update subject')
        ->name('subjects.edit');
    Route::get('/subjects/{subjectId}', SubjectDetail::class)
        ->middleware('permission:read subject')
        ->name('subjects.show');
    Route::get('/subjects/teacher/assign', \App\Livewire\Subjects\AssignTeacher::class)
        ->middleware('permission:update subject')
        ->name('subjects.assign-teacher');
    // Route::get('/subjects/assign-teacher', AssignTeacher::class)->name('subjects.assign-teacher');
});
/*
|--------------------------------------------------------------------------
| PARENT MANAGEMENT (Full Livewire)
|--------------------------------------------------------------------------
*/

use App\Livewire\Parents\{ManageParents, ParentDetail, AssignStudentsToParent};

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
    
    Route::prefix('parents')->group(function () {
        // Main parents page (list/create/edit all in one component)
        Route::get('/', ManageParents::class)
            ->middleware('permission:read parent')
            ->name('parents.index');
        
        // Assign students - IMPORTANT: This must come BEFORE {parentId}
        Route::get('/{parent}/assign-students', AssignStudentsToParent::class)
            ->middleware('permission:update parent')
            ->name('parents.assign-student');
        
        // View parent profile - This catches any ID that doesn't match above routes
        Route::get('/{parentId}', ParentDetail::class)
            ->middleware('permission:read parent')
            ->name('parents.show');
    });
});

/*
|--------------------------------------------------------------------------
| FEE MANAGEMENT (Full Livewire)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->prefix('fees')->group(function () {

    // Fee Categories
    Route::get('/fee-categories', ManageFeeCategories::class)
        ->middleware('permission:read fee category')
        ->name('fee-categories.index');
    Route::get('/fee-categories/create', ManageFeeCategories::class)
        ->middleware('permission:create fee category')
        ->name('fee-categories.create');
    Route::get('/fee-categories/{feeCategory}/edit', ManageFeeCategories::class)
        ->middleware('permission:update fee category')
        ->name('fee-categories.edit');

    // Fees
    Route::get('/', ManageFees::class)
        ->middleware('permission:read fee')
        ->name('fees.index');
    Route::get('/create', ManageFees::class)
        ->middleware('permission:create fee')
        ->name('fees.create');
    Route::get('/{fee}/edit', ManageFees::class)
        ->middleware('permission:update fee')
        ->name('fees.edit');

    // Fee Invoices
    Route::get('/fee-invoices', ManageFeeInvoices::class)
        ->middleware('permission:read fee invoice')
        ->name('fee-invoices.index');
    Route::get('/fee-invoices/create', ManageFeeInvoices::class)
        ->middleware('permission:create fee invoice')
        ->name('fee-invoices.create');
    Route::get('/fee-invoices/{feeInvoice}/edit', ManageFeeInvoices::class)
        ->middleware('permission:update fee invoice')
        ->name('fee-invoices.edit');
    Route::get('/fee-invoices/{feeInvoiceId}', FeeInvoiceDetail::class)
        ->middleware('permission:read fee invoice')
        ->name('fee-invoices.show');

    // Print invoice (keeping this as controller since it generates PDF)
    Route::get('/fee-invoices/{fee_invoice}/print', [FeeInvoiceController::class, 'print'])
        ->middleware('permission:read fee invoice')
        ->name('fee-invoices.print');
});

/*
|--------------------------------------------------------------------------
| TEACHER RESULT ROUTES
|--------------------------------------------------------------------------
*/

Route::prefix('teacher')->middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId', 'permission:upload result'])->group(function () {
    Route::get('results', fn () => redirect()->route('result.upload.individual'))->name('results.index');
});

/*
|--------------------------------------------------------------------------
| CBT ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->prefix('cbt')->name('cbt.')->group(function () {
    Route::get('/exams', CbtExamSelection::class)
        ->middleware('permission:take cbt exam')
        ->name('exams');
    Route::get('/exam/{assessment}', CbtExamInterface::class)
        ->middleware('permission:take cbt exam')
        ->name('exam.take');
    Route::get('/results', CbtViewer::class)
        ->middleware('permission:view cbt result')
        ->name('viewer');

    Route::get('/manage', CbtManagement::class)
        ->middleware('permission:manage cbt')
        ->name('manage');
});

/*
|--------------------------------------------------------------------------
| STUDENT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
    Route::get('/students', ManageStudents::class)
        ->middleware('permission:read student')
        ->name('students.index');
    Route::get('/students/create', ManageStudents::class)
        ->middleware('permission:create student')
        ->name('students.create');
    Route::get('/students/{studentId}/edit', ManageStudents::class)
        ->middleware('permission:update student')
        ->name('students.edit');
    Route::get('/students/{studentId}', StudentDetail::class)
        ->middleware('permission:read student')
        ->name('students.show');

    Route::get('/admissions/registrations', ManageAdmissionRegistrations::class)
        ->name('admissions.registrations.index')
        ->middleware('permission:read admission registration');

    Route::get('/contacts/messages', ManageContactMessages::class)
        ->name('contacts.messages.index')
        ->middleware('permission:read contact message');

    Route::get('/gallery/manage', ManageGallery::class)
        ->name('gallery.manage')
        ->middleware('permission:manage gallery');

    // Graduation routes
    Route::get('/students/graduations/manage', GraduateStudents::class)
        ->name('students.graduate')
        ->middleware('permission:graduate student');

    Route::get('/students/graduations/history', GraduateStudents::class)
        ->name('students.graduations')
        ->middleware('permission:view graduations');

    // Promotion routes
    Route::get('/students/promotions/manage', PromoteStudents::class)
        ->name('students.promote')
        ->middleware('permission:promote student');
});

/*
|--------------------------------------------------------------------------
| SECTION MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
    Route::get('/sections', ManageSections::class)
        ->middleware('permission:read section')
        ->name('sections.index');
    Route::get('/sections/{sectionId}', SectionDetail::class)
        ->middleware('permission:read section')
        ->name('sections.show');
});

/*
|--------------------------------------------------------------------------
| RESULT MANAGEMENT
|--------------------------------------------------------------------------
*/

Route::prefix('results')->middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
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
| SCHOOL MANAGEMENT (Full Livewire)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {
    
    // Schools list
    Route::get('/schools', \App\Livewire\Schools\ManageSchools::class)
        ->middleware('permission:read school|create school|update school|delete school|manage school settings')
        ->name('schools.index');
    
    // Website and school-specific public content settings.
    Route::get('/schools/settings', \App\Livewire\Schools\ManageSiteSettings::class)
        ->middleware('permission:manage school settings')
        ->name('schools.settings');
    
    // School detail - comes LAST
    Route::get('/schools/{schoolId}', \App\Livewire\Schools\SchoolDetail::class)
        ->middleware('permission:read school')
        ->name('schools.show');
});

/*
|--------------------------------------------------------------------------
| ADMIN MANAGEMENT (Full Livewire)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {

    Route::get('/users/roles', \App\Livewire\Users\ManageUserRoles::class)
        ->middleware('permission:manage user roles')
        ->name('users.roles');

    Route::get('/admins', \App\Livewire\Admins\ManageAdmins::class)
        ->middleware('permission:read admin|create admin|update admin|delete admin')
        ->name('admins.index');
    Route::get('/admins/{adminId}', \App\Livewire\Admins\AdminDetail::class)
        ->middleware('permission:read admin')
        ->name('admins.show');
        
    // Convenience routes for create/edit (they'll be handled by ManageAdmins component)
    Route::get('/admins/create', function() {
        return redirect()->route('admins.index', ['mode' => 'create']);
    })->middleware('permission:create admin')->name('admins.create');
});
/*
|--------------------------------------------------------------------------
| DASHBOARD ADMINISTRATIVE ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware($dashboardMiddleware)->prefix('dashboard')->group(function () {

    // Academic Year Management
    Route::get('academic-years', ManageAcademicYears::class)
        ->middleware('permission:read academic year')
        ->name('academic-years.index')
        ->can('viewAny', 'App\Models\AcademicYear');

    Route::get('academic-years/{academicYear}', ShowAcademicYear::class)
        ->middleware('permission:read academic year')
        ->name('academic-years.show')
        ->can('view', 'academicYear');

    // Semester Management
    Route::middleware('App\Http\Middleware\EnsureAcademicYearIsSet')->group(function () {
        Route::get('semesters', ManageSemesters::class)
            ->middleware('permission:read semester')
            ->name('semesters.index')
            ->can('viewAny', 'App\Models\Semester');
    });

    // Class Groups & Classes
    Route::get('class-groups', ManageClassGroups::class)
        ->middleware('permission:read class group')
        ->name('class-groups.index');
    Route::get('classes', ManageClasses::class)
        ->middleware('permission:read class')
        ->name('classes.index');
    Route::post('classes/{class}/assign-subjects', [MyClassController::class, 'assignSubjects'])
        ->middleware('permission:update class');
});

/*
|--------------------------------------------------------------------------
| SCHOOL Timetable ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
    Route::get('/timetables', \App\Livewire\Timetable\ManageTimetables::class)
        ->middleware('permission:read timetable')
        ->name('timetables.index');

    Route::get('/timetables/create', \App\Livewire\Timetable\ManageTimetables::class)
        ->middleware('permission:create timetable')
        ->name('timetables.create');

    Route::get('/custom-timetable-items', \App\Livewire\Timetable\ManageTimetables::class)
        ->middleware('permission:read custom timetable item')
        ->name('custom-timetable-items.index');

    Route::get('/custom-timetable-items/create', \App\Livewire\Timetable\ManageTimetables::class)
        ->middleware('permission:create custom timetable item')
        ->name('custom-timetable-items.create');

});

/*
|--------------------------------------------------------------------------
| SCHOOL ADMINISTRATION ROUTES
|--------------------------------------------------------------------------
*/


Route::middleware($adminMiddleware)->prefix('dashboard')->group(function () {

    Route::middleware('App\Http\Middleware\EnsureSuperAdminHasSchoolId')->group(function () {

        // User Management
        Route::post('users/lock-account/{user}', LockUserAccountController::class)
            ->middleware('permission:lock user')
            ->name('user.lock-account');

        // Notices
        Route::resource('notices', NoticeController::class)
            ->whereNumber('notice');

        // Attendance & Discipline
        Route::get('attendance', \App\Livewire\Attendance\ManageAttendance::class)
            ->middleware('permission:read attendance')
            ->name('attendance.index');

        Route::get('discipline', \App\Livewire\Discipline\ManageDisciplineIncidents::class)
            ->middleware('permission:read discipline incident')
            ->name('discipline.index');

        // Broadcast Messaging
        Route::get('broadcasts', \App\Livewire\Broadcasts\ManageBroadcastMessages::class)
            ->middleware('permission:read broadcast message')
            ->name('broadcasts.manage');

        // Media Library
        Route::get('media-library', \App\Livewire\Media\ManageMediaLibrary::class)
            ->middleware('permission:manage media library')
            ->name('media-library.index');

        // Academic Year Dependent Routes
        Route::middleware([
            'App\Http\Middleware\EnsureAcademicYearIsSet',
            'App\Http\Middleware\CreateCurrentAcademicYearRecord'
        ])->group(function () {

     
            // Semester Dependent Routes
            Route::middleware('App\Http\Middleware\EnsureSemesterIsSet')->group(function () {


                // Syllabi (Full Livewire)
                Route::get('syllabi', ManageSyllabi::class)
                    ->middleware('permission:read syllabus')
                    ->name('syllabi.index');
                Route::get('syllabi/create', ManageSyllabi::class)
                    ->middleware('permission:create syllabus')
                    ->name('syllabi.create');
                Route::get('syllabi/{syllabus}', ManageSyllabi::class)
                    ->middleware('permission:read syllabus')
                    ->name('syllabi.show');

   
                
                // Exams
                Route::resource('exams', ExamController::class)
                    ->only(['index', 'show'])
                    ->middleware('permission:read exam');
                Route::resource('exams', ExamController::class)
                    ->only(['create', 'store'])
                    ->middleware('permission:create exam');
                Route::resource('exams', ExamController::class)
                    ->only(['edit', 'update'])
                    ->middleware('permission:update exam');
                Route::resource('exams', ExamController::class)
                    ->only(['destroy'])
                    ->middleware('permission:delete exam');
                Route::post('exams/{exam}/set-active-status', [ExamController::class, 'setExamActiveStatus'])
                    ->middleware('permission:update exam')
                    ->name('exams.set-active-status');
                Route::post('exams/{exam}/set-publish-result-status', [ExamController::class, 'setPublishResultStatus'])
                    ->middleware('permission:update exam')
                    ->name('exams.set-publish-result-status');

                Route::resource('exams/exam-records', ExamRecordController::class)
                    ->only(['index'])
                    ->middleware('permission:read exam record');
                Route::resource('exams/exam-records', ExamRecordController::class)
                    ->only(['create', 'store'])
                    ->middleware('permission:create exam record');

                Route::scopeBindings()->group(function () {
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class)
                        ->only(['index', 'show'])
                        ->middleware('permission:read exam slot');
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class)
                        ->only(['create', 'store'])
                        ->middleware('permission:create exam slot');
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class)
                        ->only(['edit', 'update'])
                        ->middleware('permission:update exam slot');
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class)
                        ->only(['destroy'])
                        ->middleware('permission:delete exam slot');
                });

                // Exam Reports
                Route::get('exams/tabulation-sheet', [ExamController::class, 'examTabulation'])
                    ->middleware('permission:read exam')
                    ->name('exams.tabulation');
                Route::get('exams/semester-result-tabulation', [ExamController::class, 'semesterResultTabulation'])
                    ->middleware('permission:read exam')
                    ->name('exams.semester-result-tabulation');
                Route::get('exams/academic-year-result-tabulation', [ExamController::class, 'academicYearResultTabulation'])
                    ->middleware('permission:read exam')
                    ->name('exams.academic-year-result-tabulation');
                Route::get('exams/result-checker', [ExamController::class, 'resultChecker'])
                    ->middleware('permission:check result')
                    ->name('exams.result-checker');

                // Grade Systems
                Route::resource('grade-systems', GradeSystemController::class)
                    ->only(['index', 'show'])
                    ->middleware('permission:read grade system');
                Route::resource('grade-systems', GradeSystemController::class)
                    ->only(['create', 'store'])
                    ->middleware('permission:create grade system');
                Route::resource('grade-systems', GradeSystemController::class)
                    ->only(['edit', 'update'])
                    ->middleware('permission:update grade system');
                Route::resource('grade-systems', GradeSystemController::class)
                    ->only(['destroy'])
                    ->middleware('permission:delete grade system');
            });
        });
    });
});
