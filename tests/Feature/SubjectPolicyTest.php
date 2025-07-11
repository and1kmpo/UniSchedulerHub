<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subject;
use App\Models\Professor;
use App\Models\Program;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed(); // Usamos tu DatabaseSeeder con Programas cargados
    }

    /** @test */
    public function admin_can_view_any_subject()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $program = Program::first();

        $subject = Subject::factory()->create([
            'program_id' => $program->id,
        ]);

        $this->actingAs($admin)
            ->get(route('subjects.show', $subject))
            ->assertOk();
    }

    /** @test */
    public function assigned_professor_can_view_subject()
    {
        $program = Program::first();

        $subject = Subject::factory()->create([
            'program_id' => $program->id,
        ]);

        $professorUser = User::factory()->create();
        $professorUser->assignRole('professor');

        $professor = Professor::factory()->create([
            'user_id' => $professorUser->id,
        ]);

        // Asignar al profesor la materia
        $professor->subjects()->attach($subject->id);

        $this->actingAs($professorUser)
            ->get(route('subjects.show', $subject))
            ->assertOk();
    }

    /** @test */
    /** @test */
public function unassigned_professor_cannot_view_subject()
{
    $professorUser = User::factory()->create();
    $professorUser->assignRole('professor');

    // Asociar un modelo professor al user, pero sin asignarle la materia
    $professor = \App\Models\Professor::factory()->create([
        'user_id' => $professorUser->id,
    ]);

    $program = Program::inRandomOrder()->first();

    $subject = Subject::factory()->create([
        'program_id' => $program->id,
    ]);

    $this->actingAs($professorUser)
        ->get(route('subjects.show', $subject))
        ->assertForbidden(); // Aquí ahora sí debería retornar 403
}

}
