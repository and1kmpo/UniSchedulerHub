<?php

namespace Tests\Feature;

use App\Models\ClassGroup;
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

        $response = $this->post(route('class-schedules.store', ['class_group' => $group->id]), [
            'day' => 'monday',
            'start_time' => '09:00',
            'end_time' => '10:30',
            'classroom' => 'A101',
        ]);

        $response->assertRedirect(route('class-schedules.index', ['class_group' => $group->id]));
        $this->assertDatabaseHas('class_schedules', [
            'class_group_id' => $group->id,
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
            'classroom' => 'A303',
        ]);

        $response->assertSessionHasErrors(['start_time']);
        $this->assertCount(1, ClassSchedule::all());
    }

    /** @test */
    public function it_updates_a_schedule()
    {
        $group = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $group->id]);

        $response = $this->put(
            route('class-schedules.update', ['class_group' => $group->id, 'schedule' => $schedule->id]),
            [
                'day' => 'tuesday',
                'start_time' => '11:00',
                'end_time' => '12:30',
                'classroom' => 'B202',
            ]
        );

        $response->assertRedirect(route('class-schedules.index', ['class_group' => $group->id]));
        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'day' => 'tuesday',
        ]);
    }

    /** @test */
    public function it_deletes_a_schedule()
    {
        $group = ClassGroup::factory()->create();
        $schedule = ClassSchedule::factory()->create(['class_group_id' => $group->id]);

        $response = $this->delete(route('class-schedules.destroy', [
            'class_group' => $group->id,
            'schedule' => $schedule->id,
        ]));

        $response->assertRedirect(route('class-schedules.index', ['class_group' => $group->id]));
        $this->assertDatabaseMissing('class_schedules', ['id' => $schedule->id]);
    }
}
