<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        $this->call([
            RolSeeder::class,
            GradeStatusSeeder::class,
            SubjectEnrollmentStatusSeeder::class,
            AcademicPeriodSeeder::class,
            ProgramSeeder::class,
            SubjectSeeder::class,
            AdminUserSeeder::class,
            ProfessorSeeder::class,
            StudentSeeder::class,
            SubjectAssignmentSeeder::class,
            ClassGroupSeeder::class,
            ClassScheduleSeeder::class,
        ]);
    }
}
