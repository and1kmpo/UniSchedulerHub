<?php

namespace Database\Seeders;

use App\Models\Program;
use Illuminate\Database\Seeder;

class ProgramSeeder extends Seeder
{
    public function run()
    {
        $programs = [
            ['name' => 'Computer Science', 'description' => 'Study of computers and computational systems.'],
            ['name' => 'Mechanical Engineering', 'description' => 'Design and manufacture of machines and systems.'],
            ['name' => 'Business Administration', 'description' => 'Management of business operations.'],
            ['name' => 'Psychology', 'description' => 'Study of the human mind and behavior.'],
            ['name' => 'Biology', 'description' => 'Study of living organisms and their interactions.'],
            ['name' => 'Mathematics', 'description' => 'Abstract science of numbers, quantities, and shapes.'],
            ['name' => 'Civil Engineering', 'description' => 'Design and construction of infrastructure projects.'],
            ['name' => 'Physics', 'description' => 'Study of matter, energy, and the laws of nature.'],
            ['name' => 'Law', 'description' => 'Study of legal systems and the law.'],
            ['name' => 'Economics', 'description' => 'Study of production, consumption, and transfer of wealth.'],
        ];

        foreach ($programs as $program) {
            Program::create($program);
        }
    }
}
