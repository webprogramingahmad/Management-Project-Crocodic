<?php

namespace Tests\Feature\Task;

use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskCreateDuplicateTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_staff_create_task_inserts_single_record_and_renders_once(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $difficulty = TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();

        $before = Task::count();

        $this->actingAs($staff)
            ->from(route('staff.tasks.index'))
            ->post(route('staff.task.store'), [
                'name' => 'Single Staff Task',
                'id_difficulty' => $difficulty->id,
                'id_project' => $project->id,
                'description' => 'Deskripsi task.',
            ])
            ->assertRedirect(route('staff.tasks.index'));

        $this->assertSame(1, Task::count() - $before);

        $task = Task::query()->where('name', 'Single Staff Task')->firstOrFail();

        $html = $this->actingAs($staff)
            ->get(route('staff.tasks.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'data-task-id="'.$task->id.'"'));
    }

    public function test_director_create_task_inserts_single_record_and_renders_once(): void
    {
        $director = $this->makeUser('director');
        $project = $this->makeProject($director);
        $difficulty = TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();

        $before = Task::count();

        $this->actingAs($director)
            ->from(route('director.tasks.index'))
            ->post(route('director.task.store'), [
                'name' => 'Single Director Task',
                'id_difficulty' => $difficulty->id,
                'id_project' => $project->id,
                'description' => 'Deskripsi task director.',
            ])
            ->assertRedirect(route('director.tasks.index'));

        $this->assertSame(1, Task::count() - $before);

        $task = Task::query()->where('name', 'Single Director Task')->firstOrFail();

        $html = $this->actingAs($director)
            ->get(route('director.tasks.index'))
            ->assertOk()
            ->getContent();

        $this->assertSame(1, substr_count($html, 'data-task-id="'.$task->id.'"'));
    }
}
