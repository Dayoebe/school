<?php

namespace App\Providers;

use App\Models\Exam;
use App\Models\ExamPaper;
use App\Models\ExamRecord;
use App\Models\MyClass;
use App\Policies\ExamPaperPolicy;
use App\Policies\ExamPolicy;
use App\Policies\ExamRecordPolicy;
use App\Policies\MyClassPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MyClass::class => MyClassPolicy::class,
        Exam::class => ExamPolicy::class,
        ExamRecord::class => ExamRecordPolicy::class,
        ExamPaper::class => ExamPaperPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}
