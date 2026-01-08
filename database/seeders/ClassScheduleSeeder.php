<?php

namespace Database\Seeders;

use App\Models\ClassGroup;
use App\Models\ClassSchedule;
use Illuminate\Database\Seeder;

class ClassScheduleSeeder extends Seeder
{
    public function run()
    {
        $groups = ClassGroup::all();

        foreach ($groups as $group) {
            ClassSchedule::factory(rand(1, 3))->create([
                'class_group_id' => $group->id,
            ]);
        }
    }
}
