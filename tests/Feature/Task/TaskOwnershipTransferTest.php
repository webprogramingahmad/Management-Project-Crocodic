<?php

namespace Tests\Feature\Task;

use App\Models\StatusTask;
use App\Models\Task;
use App\Models\TaskOwnershipTransfer;
use App\Models\TaskOwnershipTransferRequest;
use App\Models\TaskRevisionCycle;
use App\Models\User;
use App\Support\TaskRunningTimer;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskOwnershipTransferTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_staff_owner_can_submit_ownership_request(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'progress');

        $this->actingAs($staff)
            ->from(route('staff.tasks.index'))
            ->post(route('staff.task.ownership.request', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Akan mengajukan izin besok.',
            ])
            ->assertRedirect(route('staff.tasks.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('task_ownership_transfer_requests', [
            'id_task' => $task->id,
            'from_user_id' => $staff->id,
            'to_user_id' => $replacement->id,
            'status' => TaskOwnershipTransferRequest::STATUS_PENDING,
        ]);
    }

    public function test_non_owner_staff_cannot_submit_request(): void
    {
        $director = $this->makeUser('director');
        $owner = $this->makeUser('staff');
        $other = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$owner->id, $other->id, $replacement->id]);
        $task = $this->makeTask($owner, $project, 'todo');

        $this->actingAs($other)
            ->post(route('staff.task.ownership.request', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Coba transfer.',
            ])
            ->assertForbidden();
    }

    public function test_director_can_approve_ownership_request(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'revision');

        $request = TaskOwnershipTransferRequest::create([
            'id_task' => $task->id,
            'requested_by' => $staff->id,
            'from_user_id' => $staff->id,
            'to_user_id' => $replacement->id,
            'reason' => 'Izin sakit.',
            'status' => TaskOwnershipTransferRequest::STATUS_PENDING,
        ]);

        $this->actingAs($director)
            ->from(route('director.tasks.index'))
            ->post(route('director.task.ownership.approve', [
                'id' => $task->id,
                'requestId' => $request->id,
            ]))
            ->assertRedirect(route('director.tasks.index'))
            ->assertSessionHas('success');

        $task->refresh();
        $this->assertSame((string) $replacement->id, (string) $task->id_user);
        $this->assertDatabaseHas('task_ownership_transfers', [
            'id_task' => $task->id,
            'from_user_id' => $staff->id,
            'to_user_id' => $replacement->id,
        ]);
    }

    public function test_director_can_direct_reassign(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'todo');

        $this->actingAs($director)
            ->post(route('director.task.ownership.reassign', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Staff sudah absent.',
            ])
            ->assertSessionHas('success');

        $this->assertSame((string) $replacement->id, (string) $task->fresh()->id_user);
    }

    public function test_complete_task_cannot_be_transferred(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'complete');

        $this->actingAs($staff)
            ->post(route('staff.task.ownership.request', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Tidak boleh.',
            ])
            ->assertForbidden();
    }

    public function test_transfer_resets_running_started_at_for_in_progress_task(): void
    {
        Carbon::setTestNow('2026-06-24 12:00:00');

        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'progress');
        $oldStart = now()->subHours(3);
        $task->update(['running_started_at' => $oldStart]);

        $this->actingAs($director)
            ->post(route('director.task.ownership.reassign', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Izin mendadak.',
            ])
            ->assertSessionHas('success');

        $task->refresh();
        $this->assertTrue($task->running_started_at->equalTo(now()));
        $this->assertFalse($task->running_started_at->equalTo($oldStart));

        $deadline = TaskRunningTimer::deadlineFor($task);
        $this->assertNotNull($deadline);
        $this->assertTrue($deadline->equalTo(now()->copy()->addHours(2)));

        $this->assertDatabaseHas('task_ownership_transfers', [
            'id_task' => $task->id,
            'was_overdue_at_transfer' => true,
        ]);
        $this->assertNotNull(
            TaskOwnershipTransfer::query()
                ->where('id_task', $task->id)
                ->value('timer_reset_at')
        );

        Carbon::setTestNow();
    }

    public function test_transfer_resets_revision_deadline_for_revision_task(): void
    {
        Carbon::setTestNow('2026-06-24 15:00:00');

        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'revision');
        $oldDeadline = now()->subHour();
        $task->update([
            'revision_hours' => 3,
            'revision_deadline_at' => $oldDeadline,
        ]);
        TaskRevisionCycle::query()->create([
            'id_task' => $task->id,
            'cycle_number' => 1,
            'entered_revision_at' => now()->subHours(4),
            'deadline_at' => $oldDeadline,
            'revision_hours' => 3,
            'notes' => 'Perbaiki layout.',
        ]);

        $this->actingAs($director)
            ->post(route('director.task.ownership.reassign', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Handover revision.',
            ])
            ->assertSessionHas('success');

        $task->refresh();
        $this->assertTrue($task->revision_deadline_at->equalTo(now()->copy()->addHours(3)));

        $cycle = TaskRevisionCycle::query()->where('id_task', $task->id)->first();
        $this->assertNotNull($cycle);
        $this->assertTrue($cycle->entered_revision_at->equalTo(now()));
        $this->assertTrue($cycle->deadline_at->equalTo(now()->copy()->addHours(3)));

        $this->assertDatabaseHas('task_ownership_transfers', [
            'id_task' => $task->id,
            'was_overdue_at_transfer' => true,
        ]);

        Carbon::setTestNow();
    }

    public function test_transfer_does_not_reset_timer_for_todo_task(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'todo');

        $this->actingAs($director)
            ->post(route('director.task.ownership.reassign', $task->id), [
                'to_user_id' => $replacement->id,
                'reason' => 'Belum mulai.',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('task_ownership_transfers', [
            'id_task' => $task->id,
            'was_overdue_at_transfer' => false,
            'timer_reset_at' => null,
        ]);
    }

    public function test_pending_ownership_request_shows_card_badges_for_staff_and_director(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $replacement = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach([$staff->id, $replacement->id]);
        $task = $this->makeTask($staff, $project, 'progress');

        TaskOwnershipTransferRequest::create([
            'id_task' => $task->id,
            'requested_by' => $staff->id,
            'from_user_id' => $staff->id,
            'to_user_id' => $replacement->id,
            'reason' => 'Izin cuti singkat.',
            'status' => TaskOwnershipTransferRequest::STATUS_PENDING,
        ]);

        $this->actingAs($staff)
            ->get(route('staff.tasks.index'))
            ->assertOk()
            ->assertSee('Pending approval', false);

        $this->actingAs($director)
            ->get(route('director.tasks.index'))
            ->assertOk()
            ->assertSee('Transfer review', false);
    }
}
