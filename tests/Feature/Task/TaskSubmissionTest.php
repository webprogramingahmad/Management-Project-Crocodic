<?php

namespace Tests\Feature\Task;

use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskSubmissionTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
        Storage::fake('public');
    }

    public function test_create_task_requires_description(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $difficulty = \App\Models\TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();

        $this->actingAs($staff)
            ->from(route('staff.tasks.index'))
            ->post(route('staff.task.store'), [
                'name' => 'Task tanpa deskripsi',
                'id_difficulty' => $difficulty->id,
                'id_project' => $project->id,
            ])
            ->assertSessionHasErrors(['description']);
    }

    public function test_staff_must_submit_work_before_review(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'progress');
        $review = StatusTask::query()->where('class', 'review')->firstOrFail();

        $this->actingAs($staff)
            ->postJson(route('staff.task.updateStatus', $task->id), ['id_status' => $review->id])
            ->assertStatus(422);
    }

    public function test_staff_can_submit_work_to_review(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'progress');
        $task->update(['description' => 'Kerjakan fitur login']);

        $this->actingAs($staff)
            ->post(route('staff.task.submitReview', $task->id), [
                'notes' => 'Fitur login selesai diimplementasikan.',
                'links' => "https://example.com/demo\n",
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $task->refresh();
        $this->assertSame('review', $task->status->class);
        $this->assertDatabaseHas('task_submissions', [
            'id_task' => $task->id,
            'type' => TaskSubmission::TYPE_WORK,
            'submitted_by' => $staff->id,
        ]);
    }

    public function test_submit_review_requires_notes(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'progress');

        $this->actingAs($staff)
            ->post(route('staff.task.submitReview', $task->id), [
                'notes' => '',
            ])
            ->assertSessionHasErrors(['notes']);
    }

    public function test_submit_review_accepts_optional_photo(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'progress');

        $file = UploadedFile::fake()->image('bukti.jpg');

        $this->actingAs($staff)
            ->post(route('staff.task.submitReview', $task->id), [
                'notes' => 'Sudah selesai dengan bukti.',
                'photos' => [$file],
            ])
            ->assertOk();

        $this->assertDatabaseCount('task_photos', 1);
    }

    public function test_executive_cannot_create_task(): void
    {
        $executive = $this->makeUser('executive');
        $director = $this->makeUser('director');
        $project = $this->makeProject($director);
        $difficulty = \App\Models\TaskDifficulty::query()->where('difficulty', 'Low')->firstOrFail();

        $this->actingAs($executive)
            ->post(route('executive.project.task.store', $project->id), [
                'name' => 'Task',
                'id_difficulty' => $difficulty->id,
                'description' => 'Test',
            ])
            ->assertForbidden();
    }

    public function test_submissions_api_returns_meta_timing_and_ordered_cycles(): void
    {
        Carbon::setTestNow('2026-06-24 15:00:00');

        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $task = $this->makeTask($staff, $project, 'progress');
        $task->update([
            'name' => 'Implementasi Login',
            'running_started_at' => now()->subHours(3),
        ]);

        $this->actingAs($staff)
            ->post(route('staff.task.submitReview', $task->id), [
                'notes' => 'Login selesai dikerjakan.',
                'links' => "https://example.com/demo\n",
            ])
            ->assertOk();

        $task->refresh();

        $this->actingAs($staff)
            ->getJson(route('staff.task.submissions', $task->id))
            ->assertOk()
            ->assertJsonPath('data.meta.task_name', 'Implementasi Login')
            ->assertJsonPath('data.meta.owner_name', $staff->name)
            ->assertJsonPath('data.meta.project_name', $project->name)
            ->assertJsonPath('data.work_submission.notes', 'Login selesai dikerjakan.')
            ->assertJsonPath('data.work_submission.timing.used_seconds', 3 * 3600)
            ->assertJsonPath('data.work_submission.timing.is_overdue', true)
            ->assertJsonPath('data.work_submission.timing.overdue_seconds', 3600)
            ->assertJsonPath('data.revision_cycles', []);

        Carbon::setTestNow();
    }
}
