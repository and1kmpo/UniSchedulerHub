<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
use App\Models\Classroom;
use App\Models\ClassSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClassScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('admin');
        Role::findOrCreate('professor');

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin);
    }

    /** @test */
    public function it_displays_class_group_schedules()
    {
        $group = ClassGroup::factory()->create();
        ClassSchedule::factory()->count(2)->create(['class_group_id' => $group->id]);

        $response = $this->get(route('class-schedules.index', ['class_group' => $group->id]));

        $response->assertStatus(200);
        $response->assertSee($group->name);
    }

    /** @test */
    public function it_creates_a_new_schedule()
    {
        $group = ClassGroup::factory()->create();
        $classroom = Classroom::factory()->create();

        $response = $this->post(route('class-schedules.store', ['class_group' => $group->id]), [
            'day' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'classroom_id' => $classroom->id,
        ]);

        $response->assertRedirect(route('class-groups.show', $group));
        $this->assertDatabaseHas('class_schedules', [
            'class_group_id' => $group->id,
            'classroom_id' => $classroom->id,
            'day' => 'monday',
            'start_time' => '09:00:00',
        ]);
    }

    /** @test */
    public function it_prevents_schedule_conflicts_in_the_same_group()
    {
        $group = ClassGroup::factory()->create();

        ClassSchedule::factory()->create([
            'class_group_id' => $group->id,
            'day' => 'monday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $response = $this->post(route('class-schedules.store', ['class_group' => $group->id]), [
            'day' => 'monday',
            'start_time' => '10:30',
            'end_time' => '12:00',
        ]);

        $response->assertSessionHasErrors(['schedule']);
        $this->assertCount(1, ClassSchedule::all());
    }

    /** @test */
    public function it_prevents_classroom_conflicts_across_groups()
    {
        $classroom = Classroom::factory()->create();
        $firstGroup = ClassGroup::factory()->create();
        $secondGroup = ClassGroup::factory()->create();

        ClassSchedule::factory()->create([
            'class_group_id' => $firstGroup->id,
            'classroom_id' => $classroom->id,
            'day' => 'monday',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $response = $this->post(route('class-schedules.store', ['class_group' => $secondGroup->id]), [
            'day' => 'monday',
            'start_time' => '10:30',
            'end_time' => '12:00',
            'classroom_id' => $classroom->id,
        ]);

        $response->assertSessionHasErrors(['schedule']);
        $this->assertCount(1, ClassSchedule::all());
    }

    /** @test */
    public function it_prevents_professor_conflicts_across_groups()
    {
        $professor = User::factory()->create();
        $professor->assignRole('professor');

        $firstGroup = ClassGroup::factory()->create(['professor_id' => $professor->id]);
        $secondGroup = ClassGroup::factory()->create(['professor_id' => $professor->id]);

        ClassSchedule::factory()->create([
            'class_group_id' => $firstGroup->id,
            'day' => 'wednesday',
            'start_time' => '14:00',
            'end_time' => '16:00',
        ]);

        $response = $this->post(route('class-schedules.store', ['class_group' => $secondGroup->id]), [
            'day' => 'wednesday',
            'start_time' => '15:00',
            'end_time' => '17:00',
        ]);

        $response->assertSessionHasErrors(['schedule']);
        $this->assertCount(1, ClassSchedule::all());
    }

    /** @test */
    public function it_ignores_cancelled_schedules_when_checking_conflicts()
    {
        $group = ClassGroup::factory()->create();

        ClassSchedule::factory()->create([
            'class_group_id' => $group->id,
            'day' => 'friday',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'status' => ClassSchedule::STATUS_CANCELLED,
        ]);

        $response = $this->post(route('class-schedules.store', ['class_group' => $group->id]), [
            'day' => 'friday',
            'start_time' => '09:00',
            'end_time' => '11:00',
        ]);

        $response->assertRedirect(route('class-groups.show', $group));
        $this->assertCount(2, ClassSchedule::all());
    }

    /** @test */
    public function it_updates_a_schedule()
    {
        $group = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $group->id]);
        $classroom = Classroom::factory()->create();

        $response = $this->put(
            route('class-schedules.update', ['class_group' => $group->id, 'schedule' => $schedule->id]),
            [
                'day' => 'tuesday',
                'start_time' => '11:00',
                'end_time' => '12:30',
                'classroom_id' => $classroom->id,
            ]
        );

        $response->assertRedirect(route('class-groups.show', $group));
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'day' => 'tuesday',
            'classroom_id' => $classroom->id,
        ]);
    }

    /** @test */
    public function it_updates_a_schedule_from_the_scheduler_grid_with_json_response()
    {
        $group = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $group->id]);
        $classroom = Classroom::factory()->create();

        $response = $this->putJson(
            route('class-schedules.update', ['class_group' => $group->id, 'schedule' => $schedule->id]),
            [
                'day' => 'thursday',
                'start_time' => '13:00',
                'end_time' => '14:30',
                'classroom_id' => $classroom->id,
                'status' => ClassSchedule::STATUS_PUBLISHED,
            ]
        );

        $response
            ->assertOk()
            ->assertJson(['message' => 'Schedule updated.']);

        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'day' => 'thursday',
            'start_time' => '13:00:00',
            'end_time' => '14:30:00',
            'classroom_id' => $classroom->id,
        ]);
    }

    /** @test */
    public function it_returns_json_validation_errors_when_scheduler_grid_creates_a_conflict()
    {
        $group = ClassGroup::factory()->create();
        $existingSchedule = ClassSchedule::factory()->create([
            'class_group_id' => $group->id,
            'day' => 'monday',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);
        $schedule = ClassSchedule::factory()->create([
            'class_group_id' => $group->id,
            'day' => 'tuesday',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $response = $this->putJson(
            route('class-schedules.update', ['class_group' => $group->id, 'schedule' => $schedule->id]),
            [
                'day' => 'monday',
                'start_time' => '09:00',
                'end_time' => '11:00',
                'status' => ClassSchedule::STATUS_PUBLISHED,
            ]
        );

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['schedule']);

        $this->assertDatabaseHas('class_schedules', [
            'id' => $existingSchedule->id,
            'day' => 'monday',
            'start_time' => '08:00:00',
        ]);
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'day' => 'tuesday',
            'start_time' => '08:00:00',
        ]);
    }

    /** @test */
    public function it_rejects_updates_when_the_schedule_does_not_belong_to_the_nested_group()
    {
        $firstGroup = ClassGroup::factory()->create();
        $secondGroup = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $firstGroup->id]);

        $response = $this->putJson(
            route('class-schedules.update', ['class_group' => $secondGroup->id, 'schedule' => $schedule->id]),
            [
                'day' => 'monday',
                'start_time' => '09:00',
                'end_time' => '10:00',
                'status' => ClassSchedule::STATUS_PUBLISHED,
            ]
        );

        $response->assertNotFound();

        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'class_group_id' => $firstGroup->id,
        ]);
    }

    /** @test */
    public function it_deletes_a_schedule()
    {
        $group = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $group->id]);

        $response = $this
            ->from(route('class-groups.show', $group))
            ->delete(route('class-schedules.destroy', [
                'class_group' => $group->id,
                'schedule' => $schedule->id,
            ]));

        $response->assertRedirect(route('class-groups.show', $group));
        $this->assertDatabaseMissing('class_schedules', ['id' => $schedule->id]);
    }
}
