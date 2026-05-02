<?php

namespace Tests\Feature\Task;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Concerns\BuildsTaskContext;
use Tests\TestCase;

class TaskReviewDecisionValidationTest extends TestCase
{
    use BuildsTaskContext;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseReferences();
    }

    public function test_review_decision_requires_revision_hours_for_revision_decision(): void
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
            ])
            ->assertSessionHasErrors(['revision_hours']);
    }

    public function test_review_decision_rejects_revision_hours_outside_allowed_values(): void
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
                'revision_hours' => 1,
            ])
            ->assertSessionHasErrors(['revision_hours']);
    }
}
