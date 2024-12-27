<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Program;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        $knowledgeAreas = [
            'Mathematics',
            'Computer Science',
            'Humanities',
            'Social Sciences',
            'Natural Sciences',
            'Engineering'
        ];

        return [
            'name' => $this->faker->sentence(3, true),
            'description' => $this->faker->sentence(10),
            'credits' => $this->faker->numberBetween(2, 6),
            'knowledge_area' => $this->faker->randomElement($knowledgeAreas),
            'program_id' => null, // Asignado desde el seeder
            'elective' => $this->faker->boolean(30),
        ];
    }
}
