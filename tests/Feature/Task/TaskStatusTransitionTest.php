<?php

namespace Tests\Feature\Task;

use App\Models\StatusTask;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskStatusTransitionTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_staff_can_move_own_task_from_todo_to_review_via_progress(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'todo');

        $progress = StatusTask::query()->where('class', 'progress')->firstOrFail();
        $review = StatusTask::query()->where('class', 'review')->firstOrFail();

        $this->actingAs($staff)->post(route('staff.task.updateStatus', $task->id), ['id_status' => $progress->id])->assertOk();
        $this->actingAs($staff)->post(route('staff.task.submitReview', $task->id), [
            'notes' => 'Hasil pengerjaan task selesai.',
        ])->assertOk();

        $task->refresh();
        $this->assertSame('review', $task->status->class);
    }

    public function test_staff_cannot_move_own_task_to_complete(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'review');
        $complete = StatusTask::query()->where('class', 'complete')->firstOrFail();

        $this->actingAs($staff)
            ->post(route('staff.task.updateStatus', $task->id), ['id_status' => $complete->id])
            ->assertForbidden();
    }

    public function test_complete_status_is_final_and_cannot_move_backward(): void
    {
        $director = $this->makeUser('director');
        $project = $this->makeProject($director);
        $task = $this->makeTask($director, $project, 'complete');
        $todo = StatusTask::query()->where('class', 'todo')->firstOrFail();

        $this->actingAs($director)
            ->post(route('director.task.updateStatus', $task->id), ['id_status' => $todo->id])
            ->assertForbidden();
    }

    public function test_drag_update_cannot_directly_move_task_to_revision(): void
    {
        $director = $this->makeUser('director');
        $project = $this->makeProject($director);
        $task = $this->makeTask($director, $project, 'review');
        $revision = StatusTask::query()->where('class', 'revision')->firstOrFail();

        $this->actingAs($director)
            ->post(route('director.task.updateStatus', $task->id), ['id_status' => $revision->id])
            ->assertForbidden();
    }
}
