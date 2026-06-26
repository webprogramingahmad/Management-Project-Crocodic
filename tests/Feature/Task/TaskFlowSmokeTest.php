<?php

namespace Tests\Feature\Task;

use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Support\DashboardLeftTabsQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskFlowSmokeTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_smoke_store_transfer_update_review_and_dashboard_builder(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);

        $low = TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();
        $todo = StatusTask::query()->where('class', 'todo')->firstOrFail();
        $progress = StatusTask::query()->where('class', 'progress')->firstOrFail();
        $review = StatusTask::query()->where('class', 'review')->firstOrFail();

        $this->actingAs($director)->post(route('director.task.store'), [
            'name' => 'Director new task',
            'id_difficulty' => $low->id,
            'id_project' => $project->id,
            'description' => 'Deskripsi task director.',
        ])->assertRedirect(route('director.tasks.index'));

        $this->actingAs($director)->post(route('director.task.transfer'), [
            'name' => 'Transferred task',
            'id_difficulty' => $low->id,
            'id_user' => $staff->id,
            'id_project' => $project->id,
        ])->assertRedirect(route('director.tasks.index'));

        $directorTask = Task::query()
            ->where('id_user', $director->id)
            ->where('id_status', $todo->id)
            ->latest('created_at')
            ->firstOrFail();

        $this->actingAs($director)->post(route('director.task.updateStatus', $directorTask->id), [
            'id_status' => $progress->id,
        ])->assertOk();

        $staffTask = Task::query()
            ->where('id_user', $staff->id)
            ->where('id_status', $todo->id)
            ->latest('created_at')
            ->firstOrFail();
        $staffTask->update(['id_status' => $review->id]);

        $this->actingAs($director)
            ->from(route('director.tasks.index'))
            ->post(route('director.task.reviewDecision', $staffTask->id), [
                'decision' => 'complete',
            ])->assertRedirect(route('director.tasks.index'));

        $tabs = DashboardLeftTabsQuery::build(now()->toDateString());
        $this->assertArrayHasKey('ready', $tabs);
        $this->assertArrayHasKey('standby', $tabs);
        $this->assertArrayHasKey('notready', $tabs);
        $this->assertArrayHasKey('complete', $tabs);
        $this->assertArrayHasKey('absent', $tabs);
    }
}
