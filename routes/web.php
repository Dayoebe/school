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
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\LockUserAccountController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\NoticeController;
use App\Http\Controllers\RegistrationController;



Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
Route::get('/admission', [PageController::class, 'admission'])->name('admission');
Route::get('/gallery', [PageController::class, 'gallery'])->name('gallery');



Route::get('/result', function () {
    return view('pages.result.index');
})->name('result');

Route::get('/results/print/{student}', [\App\Http\Controllers\ResultController::class, 'print'])->name('result.print');


use App\Http\Controllers\ResultController;

Route::get('/result/print/{student}/{academicYearId}/{semesterId}', [ResultController::class, 'print'])
    ->name('result.print');


Route::get('/results/view', [ResultController::class, 'viewResults'])->name('view-results');
Route::get('/get-semesters', [ResultController::class, 'getSemesters']);
Route::get('/get-subjects', [ResultController::class, 'getSubjects']);

Route::get('/result/print/{student}/{academicYearId}/{semesterId}', [ResultController::class, 'print'])->name('result.print');
Route::get('/results/annual/export', [ResultController::class, 'exportAnnualResult'])->name('result.annual.export');
Route::get('/results/class/export', [ResultController::class, 'exportClassResult'])->name('result.class.export');



Route::get('/result/student/{studentId}/{academicYearId}', [ResultController::class, 'showStudentAnnualResult'])
    ->name('result.student.annual');




Route::get(
    '/result/print-class/{academicYearId}/{semesterId}/{classId}',
    [ResultController::class, 'printClassResults']
)
    ->name('result.print-class');


// result.annual.export

Route::get('/result/annual/export/pdf', [App\Http\Controllers\ResultController::class, 'exportAnnualPdf'])
    ->name('result.annual.export.pdf');



// Add this to your existing routes
Route::get('/student-result-history/{student}', \App\Livewire\StudentResultHistory::class)
    ->name('student-result-history');






Route::get('/results/annual', [ResultController::class, 'annualClassResult'])->name('result.annual');


// Route::get('/result/student/{studentId}/{academicYearId}', [ResultController::class, 'showStudentResult'])->name('result.student');




Route::post('/classes/{class}/assign-subjects', [MyClassController::class, 'assignSubjects'])
    ->name('classes.assign-subjects');

// Add to routes/web.php
Route::post('/sections/{section}/subjects', [SectionController::class, 'attachSubjects'])
    ->name('sections.subjects.attach');

Route::delete('/sections/{section}/subjects/{subject}', [SectionController::class, 'detachSubject'])
    ->name('sections.subjects.detach');



Route::middleware(['guest'])->group(function () {
    Route::get('/register', ['App\Http\Controllers\RegistrationController', 'registerView'])->name('register');
    Route::post('/register', ['App\Http\Controllers\RegistrationController', 'register']);
});

//user must be authenticated
Route::middleware('auth:sanctum', 'verified', 'App\Http\Middleware\PreventLockAccountAccess', 'App\Http\Middleware\EnsureDefaultPasswordIsChanged', 'App\Http\Middleware\PreventGraduatedStudent')->prefix('dashboard')->namespace('App\Http\Controllers')->group(function () {
    //manage school settings
    Route::get('schools/settings', ['App\Http\Controllers\SchoolController', 'settings'])->name('schools.settings')->middleware('App\Http\Middleware\EnsureSuperAdminHasSchoolId');

    //School routes
    Route::resource('schools', SchoolController::class);
    Route::post('schools/set-school', ['App\Http\Controllers\SchoolController', 'setSchool'])->name('schools.setSchool');

    //super admin must have school id set
    Route::middleware(['App\Http\Middleware\EnsureSuperAdminHasSchoolId'])->group(function () {
        //dashboard route
        Route::get('/', function () {
            return view('dashboard');
        })->name('dashboard')->withoutMiddleware(['App\Http\Middleware\PreventGraduatedStudent']);

        //class routes
        Route::resource('classes', MyClassController::class);

        //class groups routes
        Route::resource('class-groups', ClassGroupController::class);

        //sections routes
        Route::resource('sections', SectionController::class);

        Route::middleware(['App\Http\Middleware\EnsureAcademicYearIsSet', 'App\Http\Middleware\CreateCurrentAcademicYearRecord'])->group(function () {
            Route::get('account-applications/rejected-applications', ['App\Http\Controllers\AccountApplicationController', 'rejectedApplicationsView'])->name('account-applications.rejected-applications');

            //account application routes. We need the applicant instead of the record
            Route::resource('account-applications', AccountApplicationController::class)->parameters([
                'account-applications' => 'applicant',
            ]);

            Route::get('account-applications/change-status/{applicant}', ['App\Http\Controllers\AccountApplicationController', 'changeStatusView'])->name('account-applications.change-status');

            Route::post('account-applications/change-status/{applicant}', ['App\Http\Controllers\AccountApplicationController', 'changeStatus']);

            //promotion routes
            Route::get('students/promotions', ['App\Http\Controllers\PromotionController', 'index'])->name('students.promotions');
            Route::get('students/promote', ['App\Http\Controllers\PromotionController', 'promoteView'])->name('students.promote');
            Route::post('students/promote', ['App\Http\Controllers\PromotionController', 'promote']);
            Route::get('students/promotions/{promotion}', ['App\Http\Controllers\PromotionController', 'show'])->name('students.promotions.show');
            Route::delete('students/promotions/{promotion}/reset', ['App\Http\Controllers\PromotionController', 'resetPromotion'])->name('students.promotions.reset');

            //graduation routes
            Route::get('students/graduations', ['App\Http\Controllers\GraduationController', 'index'])->name('students.graduations');
            Route::get('students/graduate', ['App\Http\Controllers\GraduationController', 'graduateView'])->name('students.graduate');
            Route::post('students/graduate', ['App\Http\Controllers\GraduationController', 'graduate']);
            Route::delete('students/graduations/{student}/reset', ['App\Http\Controllers\GraduationController', 'resetGraduation'])->name('students.graduations.reset');

            //semester routes
            Route::resource('semesters', SemesterController::class);
            Route::post('semesters/set', ['App\Http\Controllers\SemesterController', 'setSemester'])->name('semesters.set-semester');

            Route::middleware(['App\Http\Middleware\EnsureSemesterIsSet'])->group(function () {
                //fee categories routes
                Route::resource('fees/fee-categories', FeeCategoryController::class);

                //fee invoice record routes
                Route::post('fees/fee-invoices/fee-invoice-records/{fee_invoice_record}/pay', ['App\Http\Controllers\FeeInvoiceRecordController', 'pay'])->name('fee-invoices-records.pay');
                Route::resource('fees/fee-invoices/fee-invoice-records', FeeInvoiceRecordController::class);

                //fee incvoice routes
                Route::get('fees/fee-invoices/{fee_invoice}/pay', ['App\Http\Controllers\FeeInvoiceController', 'payView'])->name('fee-invoices.pay');
                Route::get('fees/fee-invoices/{fee_invoice}/print', ['App\Http\Controllers\FeeInvoiceController', 'print'])->name('fee-invoices.print');
                Route::resource('fees/fee-invoices', FeeInvoiceController::class);

                //fee routes
                Route::resource('fees', FeeController::class);

                //syllabi route
                Route::resource('syllabi', SyllabusController::class);

                //timetable route
                Route::resource('timetables', TimetableController::class);
                Route::resource('custom-timetable-items', CustomTimetableItemController::class);

                //manage timetable
                Route::get('timetables/{timetable}/manage', ['App\Http\Controllers\TimetableController', 'manage'])->name('timetables.manage');
                Route::get('timetables/{timetable}/print', ['App\Http\Controllers\TimetableController', 'print'])->name('timetables.print');

                //timetable-timeslot route
                Route::resource('timetables/manage/time-slots', TimetableTimeSlotController::class);
                Route::post('timetables/manage/time-slots/{time_slot}/record/create', ['App\Http\Controllers\TimetableTimeSlotController', 'addTimetableRecord'])->name('timetables.records.create')->scopeBindings();

                //set exam status
                Route::post('exams/{exam}/set--active-status', ['App\Http\Controllers\ExamController', 'setExamActiveStatus'])->name('exams.set-active-status');

                // set publish result status
                Route::post('exams/{exam}/set-publish-result-status', ['App\Http\Controllers\ExamController', 'setPublishResultStatus'])->name('exams.set-publish-result-status');
                //manage exam record
                Route::resource('exams/exam-records', ExamRecordController::class);

                //exam tabulation sheet
                Route::get('exams/tabulation-sheet', ['App\Http\Controllers\ExamController', 'examTabulation'])->name('exams.tabulation');

                //result tabulation sheet
                Route::get('exams/semester-result-tabulation', ['App\Http\Controllers\ExamController', 'semesterResultTabulation'])->name('exams.semester-result-tabulation');
                Route::get('exams/academic-year-result-tabulation', ['App\Http\Controllers\ExamController', 'academicYearResultTabulation'])->name('exams.academic-year-result-tabulation');

                //result checker
                Route::get('exams/result-checker', ['App\Http\Controllers\ExamController', 'resultChecker'])->name('exams.result-checker');

                //exam routes
                Route::resource('exams', ExamController::class);

                //exam slot routes
                Route::scopeBindings()->group(function () {
                    Route::resource('exams/{exam}/manage/exam-slots', ExamSlotController::class);
                });

                //grade system routes
                Route::resource('grade-systems', GradeSystemController::class);
            });
        });

        //student routes
        Route::resource('students', StudentController::class);
        Route::get('students/{student}/print', ['App\Http\Controllers\StudentController', 'printProfile'])->name('students.print-profile')->withoutMiddleware(['App\Http\Middleware\PreventGraduatedStudent']);

        //admin routes
        Route::resource('admins', AdminController::class);

        //teacher routes
        Route::resource('teachers', TeacherController::class);

        //parent routes
        Route::resource('parents', ParentController::class);
        Route::get('parents/{parent}/assign-student-to-parent', ['App\Http\Controllers\ParentController', 'assignStudentsView'])->name('parents.assign-student');
        Route::post('parents/{parent}/assign-student-to-parent', ['App\Http\Controllers\ParentController', 'assignStudent']);

        //lock account route
        Route::post('users/lock-account/{user}', 'App\Http\Controllers\LockUserAccountController')->name('user.lock-account');

        //academic year routes
        Route::resource('academic-years', AcademicYearController::class);
        Route::post('academic-years/set', ['App\Http\Controllers\AcademicYearController', 'setAcademicYear'])->name('academic-years.set-academic-year');

        //assign teachers to subject in class
        Route::get('subjects/assign-teacher', ['App\Http\Controllers\SubjectController', 'assignTeacherVIew'])->name('subjects.assign-teacher');
        Route::post('subjects/assign-teacher/{teacher}', ['App\Http\Controllers\SubjectController', 'assignTeacher'])->name('subjects.assign-teacher-to-subject');

        //subject routes
        Route::resource('subjects', SubjectController::class);

        //notice routes
        Route::resource('notices', NoticeController::class);
    });
});


// Route::get('/', function () {
//     return redirect()->route('dashboard');
// })->name('home');

// Route::get('/home', function () {
//     return redirect()->route('dashboard');
// });