<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Subject;
use App\Models\Professor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    /** @test */
    public function admin_can_view_any_subject()
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $subject = Subject::factory()->create();

        $this->actingAs($admin)
            ->get(route('subjects.show', $subject))
            ->assertOk();
    }

    /** @test */
    public function assigned_professor_cannot_access_administrative_subject_show()
    {
        $subject = Subject::factory()->create();

        $professorUser = User::factory()->create();
        $professorUser->assignRole('professor');

        $professor = Professor::factory()->create([
            'user_id' => $professorUser->id,
        ]);

        $professor->subjects()->attach($subject->id);

        $this->actingAs($professorUser)
            ->get(route('subjects.show', $subject))
            ->assertForbidden();
    }

    /** @test */
    public function unassigned_professor_cannot_view_subject()
    {
        $professorUser = User::factory()->create();
        $professorUser->assignRole('professor');

        Professor::factory()->create([
            'user_id' => $professorUser->id,
        ]);

        $subject = Subject::factory()->create();

        $this->actingAs($professorUser)
            ->get(route('subjects.show', $subject))
            ->assertForbidden();
    }
}
