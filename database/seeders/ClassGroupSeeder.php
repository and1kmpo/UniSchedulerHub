<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClassGroup;
use App\Models\ClassSchedule;

class ClassGroupSeeder extends Seeder
{
    public function run(): void
    {
        $classGroups = ClassGroup::factory(10)->create();

        $classGroups->each(function ($group) {
            ClassSchedule::factory(rand(1, 3))->create([
                'class_group_id' => $group->id
            ]);
        });
    }
}
