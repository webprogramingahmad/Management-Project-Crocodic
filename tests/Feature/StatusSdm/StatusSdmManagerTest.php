<?php

namespace Tests\Feature\StatusSdm;

use App\Models\Administration;
use App\Models\CategoryAdministration;
use App\Models\StatusAdministration;
use App\Models\Statussdm;
use App\Support\StatusSdmManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class StatusSdmManagerTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_daily_reset_sets_operational_user_to_not_ready(): void
    {
        $staff = $this->makeUser('staff');
        $ready = Statussdm::query()->where('status_sdm', 'Ready')->firstOrFail();
        $staff->update(['id_activity_status_sdm' => $ready->id]);

        StatusSdmManager::dailyOperationalReset();

        $staff->refresh();
        $this->assertSame('Not Ready', $staff->activityStatussdm?->status_sdm);
    }

    public function test_revision_task_marks_user_ready_and_overrides_standby(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);

        $this->makeTask($staff, $project, 'todo', 'Stand By');
        $this->makeTask($staff, $project, 'revision', 'Low');

        StatusSdmManager::syncForUser($staff);
        $staff->refresh();

        $this->assertSame('Ready', $staff->activityStatussdm?->status_sdm);
    }

    public function test_standby_task_is_only_effective_on_the_same_day(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);

        $standByTask = $this->makeTask($staff, $project, 'todo', 'Stand By');
        $standByTask->forceFill(['created_at' => now()->subDay(), 'updated_at' => now()->subDay()])->save();

        StatusSdmManager::syncForUser($staff);
        $staff->refresh();

        $this->assertSame('Not Ready', $staff->activityStatussdm?->status_sdm);
    }

    public function test_completed_work_task_from_previous_day_keeps_user_not_ready_after_sync(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);

        $completedTask = $this->makeTask($staff, $project, 'complete', 'Low');
        $completedTask->forceFill([
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ])->save();

        StatusSdmManager::syncForUser($staff);
        $staff->refresh();

        $this->assertSame('Not Ready', $staff->activityStatussdm?->status_sdm);
    }

    public function test_completed_work_task_today_sets_user_standby(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);

        $this->makeTask($staff, $project, 'complete', 'Low');

        StatusSdmManager::syncForUser($staff);
        $staff->refresh();

        $this->assertSame('Stand By', $staff->activityStatussdm?->status_sdm);
    }

    public function test_absent_has_highest_priority_over_other_conditions(): void
    {
        $director = $this->makeUser('director');
        $staff = $this->makeUser('staff');
        $project = $this->makeProject($director);
        $project->sdms()->attach($staff->id);
        $this->makeTask($staff, $project, 'progress', 'Low');

        $category = CategoryAdministration::query()->firstOrCreate(['name' => 'Leave']);
        $accepted = StatusAdministration::query()->firstOrCreate(['name' => 'accept']);
        Administration::query()->create([
            'id_user' => $staff->id,
            'id_category' => $category->id,
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'id_status' => $accepted->id,
        ]);

        StatusSdmManager::syncForUser($staff);
        $staff->refresh();

        $this->assertSame('Absent', $staff->activityStatussdm?->status_sdm);
    }
}
