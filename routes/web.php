<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PageController;
use App\Http\Controllers\MyClassController;
use App\Http\Controllers\SchoolController;
use App\Http\Controllers\ClassGroupController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\AccountApplicationController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\GraduationController;
use App\Http\Controllers\SemesterController;
use App\Http\Controllers\FeeCategoryController;
use App\Http\Controllers\FeeInvoiceRecordController;
use App\Http\Controllers\FeeInvoiceController;
use App\Http\Controllers\FeeController;
use App\Http\Controllers\SyllabusController;
use App\Http\Controllers\TimetableController;
use App\Http\Controllers\CustomTimetableItemController;
use App\Http\Controllers\TimetableTimeSlotController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\ExamRecordController;
use App\Http\Controllers\ExamSlotController;
use App\Http\Controllers\GradeSystemController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Api\TeacherController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\LockUserAccountController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth; // Import Auth facade
use Illuminate\Support\Facades\Artisan;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Public Routes (accessible to guests)
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [AuthController::class, 'login']);
    
    Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
    Route::post('register', [AuthController::class, 'register']);
    
    Route::get('forgot-password', [AuthController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');
    
    Route::get('change-password/{token}', [AuthController::class, 'showResetForm'])->name('password.reset');
    Route::post('change-password', [AuthController::class, 'resetPassword'])->name('password.update');

    // Registration routes (if different from AuthController register)
    Route::get('/register', [RegistrationController::class, 'registerView'])->name('register');
    Route::post('/register', [RegistrationController::class, 'register']);
});

// Home Route - This will now directly show the PageController@home view
// No automatic redirection to login or dashboard from the root URL
Route::get('/', [PageController::class, 'home'])->name('home');


// Authenticated Routes (accessible only to logged-in users)
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');
    
    // Main Dashboard Route - All roles redirect here after login
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Password change routes
    Route::get('/change-password', [ProfileController::class, 'showChangePasswordForm'])->name('password.change');
    Route::post('/change-password', [ProfileController::class, 'changePassword'])->name('password.update');

    // Teacher-specific routes (these are accessed once on the teacher dashboard)
    Route::prefix('teacher')->middleware('role:teacher')->group(function () {
        // The main dashboard handles the initial teacher view, so no separate teacher.dashboard route needed here
        Route::get('/classes', [TeacherController::class, 'classes'])->name('teacher.classes');
        Route::get('/classes/{class}', [TeacherController::class, 'showClass'])->name('teacher.classes.show');
        Route::get('/results/subject/{subject}', [TeacherController::class, 'subjectResults'])->name('teacher.results.subject');
        Route::get('/class-students', [TeacherController::class, 'getClassStudents'])->name('teacher.class-students');
        
        // Result management
        Route::resource('results', ResultController::class)->except(['show']);

        // API routes for teacher dashboard
        Route::get('/subjects/{subject}/students', [\App\Http\Controllers\Api\TeacherController::class, 'getSubjectStudents'])->name('api.teacher.subject.students');
        Route::get('/subjects/{subject}/results-for-upload', [\App\Http\Controllers\Api\TeacherController::class, 'getResultsForUpload'])->name('api.teacher.results.for-upload');
        Route::post('/results/bulk-upload', [\App\Http\Controllers\Api\TeacherController::class, 'bulkUploadResults'])->name('api.teacher.results.bulk-upload');
    });

    // Student performance routes (teacher specific)
    Route::middleware(['role:teacher'])->group(function () {
        Route::get('/teacher/students-in-subjects', [App\Http\Controllers\Api\TeacherController::class, 'getStudentsInSubjects'])->name('teacher.students-in-subjects');
        Route::get('/teacher/student/{student}/performance', [App\Http\Controllers\Api\TeacherController::class, 'getStudentPerformance'])->name('teacher.student-performance');
    });

    // Main dashboard prefixed routes for specific roles (if needed, otherwise handled by DashboardController)
    // These routes are redundant if DashboardController handles all role-based views at /dashboard
    // Route::prefix('student')->middleware('role:student')->group(function () {
    //     Route::get('/dashboard', [StudentController::class, 'dashboard'])->name('student.dashboard');
    // });
    // Route::prefix('parent')->middleware('role:parent')->group(function () {
    //     Route::get('/dashboard', [ParentController::class, 'dashboard'])->name('parent.dashboard');
    // });
    // Route::prefix('admin')->middleware('role:admin')->group(function () {
    //     Route::get('/dashboard', function () { return view('dashboard.admin'); })->name('admin.dashboard');
    // });


    // User must be authenticated and pass additional middleware checks for these routes
    Route::middleware([
        'auth:sanctum',
        'verified',
        'App\Http\Middleware\PreventLockAccountAccess',
        'App\Http\Middleware\EnsureDefaultPasswordIsChanged',
        'App\Http\Middleware\PreventGraduatedStudent'
    ])->prefix('dashboard')->namespace('App\Http\Controllers')->group(function () {
        // School settings (super admin specific)
        Route::get('schools/settings', ['App\Http\Controllers\SchoolController', 'settings'])
            ->name('schools.settings')
            ->middleware('App\Http\Middleware\EnsureSuperAdminHasSchoolId');

        // School routes
        Route::resource('schools', SchoolController::class);
        Route::post('schools/set-school', ['App\Http\Controllers\SchoolController', 'setSchool'])->name('schools.setSchool');

        // Routes requiring super admin to have school ID set
        Route::middleware(['App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
            // Dashboard route (already defined above as /dashboard, this might be redundant if prefix is 'dashboard')
            // If this is intended to be /dashboard/dashboard, then it's fine, otherwise remove.
            // Given the user's intent, the top-level /dashboard route is preferred.
            // Route::get('/', function () {
            //     return view('dashboard');
            // })->name('dashboard')->withoutMiddleware(['App\Http\Middleware\PreventGraduatedStudent']);

            // Class routes
            Route::resource('classes', MyClassController::class);

            // Class groups routes
            Route::resource('class-groups', ClassGroupController::class);

            // Sections routes
            Route::resource('sections', SectionController::class);

            Route::middleware(['App\Http\Middleware\EnsureAcademicYearIsSet', 'App\Http\Middleware\CreateCurrentAcademicYearRecord'])->group(function () {
                Route::get('account-applications/rejected-applications', ['App\Http\Controllers\AccountApplicationController', 'rejectedApplicationsView'])->name('account-applications.rejected-applications');

                // Account application routes. We need the applicant instead of the record
                Route::resource('account-applications', AccountApplicationController::class)->parameters([
                    'account-applications' => 'applicant',
                ]);

                Route::get('account-applications/change-status/{applicant}', ['App\Http\Controllers\AccountApplicationController', 'changeStatusView'])->name('account-applications.change-status');
                Route::post('account-applications/change-status/{applicant}', ['App\Http\Controllers\AccountApplicationController', 'changeStatus']);

                // Promotion routes
                Route::get('students/promotions', ['App\Http\Controllers\PromotionController', 'index'])->name('students.promotions');
                Route::get('students/promote', ['App\Http\Controllers\PromotionController', 'promoteView'])->name('students.promote');
                Route::post('students/promote', ['App\Http\Controllers\PromotionController', 'promote']);
                Route::get('students/promotions/{promotion}', ['App\Http\Controllers\PromotionController', 'show'])->name('students.promotions.show');
                Route::delete('students/promotions/{promotion}/reset', ['App\Http\Controllers\PromotionController', 'resetPromotion'])->name('students.promotions.reset');

                // Graduation routes
                Route::get('students/graduations', ['App\Http\Controllers\GraduationController', 'index'])->name('students.graduations');
                Route::get('students/graduate', ['App\Http\Controllers\GraduationController', 'graduateView'])->name('students.graduate');
                Route::post('students/graduate', ['App\Http\Controllers\GraduationController', 'graduate']);
                Route::delete('students/graduations/{student}/reset', ['App\Http\Controllers\GraduationController', 'resetGraduation'])->name('students.graduations.reset');

                // Semester routes
                Route::resource('semesters', SemesterController::class);
                Route::post('semesters/set', ['App\Http\Controllers\SemesterController', 'setSemester'])->name('semesters.set-semester');

                Route::middleware(['App\Http\Middleware\EnsureSemesterIsSet'])->group(function () {
                    // Fee categories routes
                    Route::resource('fees/fee-categories', FeeCategoryController::class);

                    // Fee invoice record routes
                    Route::post('fees/fee-invoices/fee-invoice-records/{fee_invoice_record}/pay', ['App\Http\Controllers\FeeInvoiceRecordController', 'pay'])->name('fee-invoices-records.pay');
                    Route::resource('fees/fee-invoices/fee-invoice-records', FeeInvoiceRecordController::class);

                    // Fee invoice routes
                    Route::get('fees/fee-invoices/{fee_invoice}/pay', ['App\Http\Controllers\FeeInvoiceController', 'payView'])->name('fee-invoices.pay');
                    Route::get('fees/fee-invoices/{fee_invoice}/print', ['App\Http\Controllers\FeeInvoiceController', 'print'])->name('fee-invoices.print');
                    Route::resource('fees/fee-invoices', FeeInvoiceController::class);

                    // Fee routes
                    Route::resource('fees', FeeController::class);

                    // Syllabi route
                    Route::resource('syllabi', SyllabusController::class);

                    // Timetable route
                    Route::resource('timetables', TimetableController::class);
                    Route::resource('custom-timetable-items', CustomTimetableItemController::class);

                    // Manage timetable
                    Route::get('timetables/{timetable}/manage', ['App\Http\Controllers\TimetableController', 'manage'])->name('timetables.manage');
                    Route::get('timetables/{timetable}/print', ['App\Http\Controllers\TimetableController', 'print'])->name('timetables.print');

                    // Timetable-timeslot route
                    Route::resource('timetables/manage/time-slots', TimetableTimeSlotController::class);
                    Route::post('timetables/manage/time-slots/{time_slot}/record/create', ['App\Http\Controllers\TimetableTimeSlotController', 'addTimetableRecord'])->name('timetables.records.create')->scopeBindings();

                    // Set exam status
                    Route::post('exams/{exam}/set--active-status', ['App\Http\Controllers\ExamController', 'setExamActiveStatus'])->name('exams.set-active-status');

                    // Set publish result status
                    Route::post('exams/{exam}/set-publish-result-status', ['App\Http\Controllers\ExamController', 'setPublishResultStatus'])->name('exams.set-publish-result-status');
                    // Manage exam record
                    Route::resource('exams/exam-records', ExamRecordController::class);

                    // Exam tabulation sheet
                    Route::get('exams/tabulation-sheet', ['App\Http\Controllers\ExamController', 'examTabulation'])->name('exams.tabulation');

                    // Result tabulation sheet
                    Route::get('exams/semester-result-tabulation', ['App\Http\Controllers\ExamController', 'semesterResultTabulation'])->name('exams.semester-result-tabulation');
                    Route::get('exams/academic-year-result-tabulation', ['App\Http\Controllers\ExamController', 'academicYearResultTabulation'])->name('exams.academic-year-result-tabulation');

                    // Result checker
                    Route::get('exams/result-checker', ['App\Http\Controllers\ExamController', 'resultChecker'])->name('exams.result-checker');

                    // Exam routes
                    Route::resource('exams', ExamController::class);

                    // Exam slot routes
                    Route::scopeBindings()->group(function () {
                        Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class);
                    });

                    // Grade system routes
                    Route::resource('grade-systems', GradeSystemController::class);
                });
            });

            // Student routes
            Route::resource('students', StudentController::class);
            Route::get('students/{student}/print', ['App\Http\Controllers\StudentController', 'printProfile'])
                ->name('students.print-profile')
                ->withoutMiddleware(['App\Http\Middleware\PreventGraduatedStudent']);

            // Admin routes
            Route::resource('admins', AdminController::class);

            // Teacher routes
            Route::resource('teachers', TeacherController::class);

            // Parent routes
            Route::resource('parents', ParentController::class);
            Route::get('parents/{parent}/assign-student-to-parent', ['App\Http\Controllers\ParentController', 'assignStudentsView'])->name('parents.assign-student');
            Route::post('parents/{parent}/assign-student-to-parent', ['App\Http\Controllers\ParentController', 'assignStudent']);

            // Lock account route
            Route::post('users/lock-account/{user}', 'App\Http\Controllers\LockUserAccountController')->name('user.lock-account');

            // Academic year routes
            Route::resource('academic-years', AcademicYearController::class);
            Route::post('academic-years/set', ['App\Http\Controllers\AcademicYearController', 'setAcademicYear'])->name('academic-years.set-academic-year');

            // Assign teachers to subject in class
            Route::get('subjects/assign-teacher', ['App\Http\Controllers\SubjectController', 'assignTeacherVIew'])->name('subjects.assign-teacher');
            Route::post('subjects/assign-teacher/{teacher}', ['App\Http\Controllers\SubjectController', 'assignTeacher'])->name('subjects.assign-teacher-to-subject');

            // Subject routes
            Route::resource('subjects', SubjectController::class);

            // Notice routes
            Route::resource('notices', NoticeController::class);
        });
    });
});

// Artisan Commands (consider protecting these with middleware for production)
Route::prefix('artisan-commands')->group(function () {
    Route::get('/clean-invalid-results', function () {
        try {
            Artisan::call('app:clean-invalid-results');
            $output = Artisan::output();
            return "Command 'app:clean-invalid-results' executed successfully:<pre>{$output}</pre>";
        } catch (\Exception $e) {
            return "Error executing 'app:clean-invalid-results': " . $e->getMessage();
        }
    })->name('artisan.clean-invalid-results');

    Route::get('/cleanup-deleted-students', function () {
        try {
            Artisan::call('cleanup:deleted-students', ['--force' => true]);
            $output = Artisan::output();
            return "Command 'cleanup:deleted-students' executed successfully:<pre>{$output}</pre>";
        } catch (\Exception $e) {
            return "Error executing 'cleanup:deleted-students': " . $e->getMessage();
        }
    })->name('artisan.cleanup-deleted-students');

    Route::get('/password-default', function () {
        try {
            Artisan::call('password:default');
            $output = Artisan::output();
            return "Command 'password:default' executed successfully:<pre>{$output}</pre>";
        } catch (\Exception $e) {
            return "Error executing 'password:default': " . $e->getMessage();
        }
    })->name('artisan.password-default');
});

// General Page Routes
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/admission', [PageController::class, 'admission'])->name('admission');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');

// Result related routes
Route::get('/result', function () {
    return view('pages.result.index');
})->name('result');
Route::get('/results/print/{student}', [ResultController::class, 'print'])->name('result.print');
Route::get('/result/print/{student}/{academicYearId}/{semesterId}', [ResultController::class, 'print'])->name('result.print');
Route::get('/results/view', [ResultController::class, 'viewResults'])->name('view-results');
Route::get('/get-semesters', [ResultController::class, 'getSemesters']);
Route::get('/get-subjects', [ResultController::class, 'getSubjects']);
Route::get('/results/annual/export', [ResultController::class, 'exportAnnualResult'])->name('result.annual.export');
Route::get('/results/class/export', [ResultController::class, 'exportClassResult'])->name('result.class.export');
Route::get('/result/student/{studentId}/{academicYearId}', [ResultController::class, 'showStudentAnnualResult'])->name('result.student.annual');
Route::get('/result/print-class/{academicYearId}/{semesterId}/{classId}', [ResultController::class, 'printClassResults'])->name('result.print-class');
Route::get('/result/annual/export/pdf', [ResultController::class, 'exportAnnualPdf'])->name('result.annual.export.pdf');
Route::get('/student-result-history/{student}', \App\Livewire\StudentResultHistory::class)->name('student-result-history');
Route::get('/results/annual', [ResultController::class, 'annualClassResult'])->name('result.annual');
Route::get('/clean-invalid-results', function () {
    $invalidResults = \App\Models\Result::whereDoesntHave('student', function($q) {
        $q->whereHas('studentSubjects', function($q) {
            $q->whereColumn('subjects.id', 'results.subject_id');
        });
    })->get();
    $count = $invalidResults->count();
    $invalidResults->each->delete();
    return "Deleted {$count} invalid results where subjects weren't assigned to students.";
});

Route::post('/classes/{class}/assign-subjects', [MyClassController::class, 'assignSubjects'])->name('classes.assign-subjects');
Route::post('/sections/{section}/subjects', [SectionController::class, 'attachSubjects'])->name('sections.subjects.attach');
Route::delete('/sections/{section}/subjects/{subject}', [SectionController::class, 'detachSubject'])->name('sections.subjects.detach');
