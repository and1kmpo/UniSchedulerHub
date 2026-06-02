<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolSeeder::class,
            AdminUserSeeder::class,
            GradeStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class,
            AcademicPeriodStatusSeeder::class,
            AcademicPeriodStatusTransitionSeeder::class,
            SubjectEnrollmentStatusTransitionSeeder::class,
            DemoAcademicSeeder::class,
        ]);
    }
}
