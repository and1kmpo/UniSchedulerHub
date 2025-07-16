<?php

namespace Database\Factories;

use App\Models\ClassSchedule;
use App\Models\ClassGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ClassScheduleFactory extends Factory
{
    protected $model = ClassSchedule::class;

    public function definition()
    {
        $start = $this->faker->dateTimeBetween('08:00', '18:00');
        $end = (clone $start)->modify('+1 hour');

        return [
            'class_group_id' => ClassGroup::inRandomOrder()->first()?->id ?? ClassGroup::factory(),
            'day' => $this->faker->randomElement(['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
            'start_time' => $start->format('H:i'),
            'end_time' => $end->format('H:i'),
            'classroom' => $this->faker->bothify('Aula-###'),
        ];
    }
}
