<?php

namespace App\Providers;

use App\Models\ClassGroup;
use App\Models\Grade;
use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\SubjectEnrollment;
use App\Policies\AcademicPeriodPolicy;
use App\Policies\ClassGroupPolicy;
use App\Policies\GradePolicy;
use App\Policies\SubjectEnrollmentPolicy;
use App\Policies\SubjectPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Subject::class => SubjectPolicy::class,
        AcademicPeriod::class => AcademicPeriodPolicy::class,
        ClassGroup::class => ClassGroupPolicy::class,
        Grade::class => GradePolicy::class,
        SubjectEnrollment::class => SubjectEnrollmentPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('edit schedule', function ($user) {
            return $user->hasRole('admin');
        });
    }
}
