<?php

namespace Tests\Feature\Task;

use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskRoleAccessTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_executive_is_monitor_only_and_cannot_update_status(): void
    {
        $executive = $this->makeUser('executive');
        $director = $this->makeUser('director');
        $project = $this->makeProject($director);
        $task = $this->makeTask($director, $project, 'todo');
        $progress = StatusTask::query()->where('class', 'progress')->firstOrFail();

        $this->actingAs($executive)
            ->post(route('executive.task.updateStatus', $task->id), ['id_status' => $progress->id])
            ->assertForbidden();
    }

    public function test_director_can_decide_staff_review_task_in_own_project(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'review');

        $this->actingAs($director)
            ->from(route('director.tasks.index'))
            ->post(route('director.task.reviewDecision', $task->id), [
                'decision' => 'revision',
                'revision_hours' => 2,
                'revision_notes' => 'Perlu perbaikan pada layout halaman.',
            ])
            ->assertRedirect(route('director.tasks.index'));

        $task->refresh();
        $this->assertSame('revision', $task->status->class);
    }

    public function test_director_review_decision_rejects_non_review_task(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'todo');

        $this->actingAs($director)
            ->from(route('director.tasks.index'))
            ->post(route('director.task.reviewDecision', $task->id), ['decision' => 'complete'])
            ->assertRedirect(route('director.tasks.index'))
            ->assertSessionHas('error');

        $task->refresh();
        $this->assertSame('todo', $task->status->class);
    }

    public function test_staff_cannot_update_other_user_task(): void
    {
        $director = $this->makeUser('director');
        $staffA = $this->makeUser('staff');
        $staffB = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staffA->id, $staffB->id]);
        $task = $this->makeTask($staffB, $project, 'todo');
        $progress = StatusTask::query()->where('class', 'progress')->firstOrFail();

        $this->actingAs($staffA)
            ->post(route('staff.task.updateStatus', $task->id), ['id_status' => $progress->id])
            ->assertForbidden();
    }

    public function test_staff_cannot_create_task_on_unassigned_project(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $beforeCount = Task::query()->count();

        $this->actingAs($staff)
            ->post(route('staff.task.store'), [
                'name' => 'Unauthorized project task',
                'id_difficulty' => TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail()->id,
                'id_project' => $project->id,
                'description' => 'Deskripsi task untuk uji akses.',
            ])
            ->assertForbidden();

        $this->assertSame($beforeCount, Task::query()->count());
    }

    public function test_director_cannot_transfer_task_to_project_outside_scope(): void
    {
        $ownerDirector = $this->makeUser('director');
        $otherDirector = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($ownerDirector);
        $project->sdms()->attach($staff->id);
        $low = TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();
        $beforeCount = Task::query()->count();

        $this->actingAs($otherDirector)
            ->post(route('director.task.transfer'), [
                'name' => 'Unauthorized transfer',
                'id_difficulty' => $low->id,
                'id_user' => $staff->id,
                'id_project' => $project->id,
            ])
            ->assertForbidden();

        $this->assertSame($beforeCount, Task::query()->count());
    }
}
