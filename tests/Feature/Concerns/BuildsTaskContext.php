<?php

namespace Tests\Feature\Concerns;

use App\Models\Division;
use App\Models\Project;
use App\Models\Role;
use App\Models\StatusProject;
use App\Models\StatusTask;
use App\Models\Statussdm;
use App\Models\Task;
use App\Models\TaskDifficulty;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

trait BuildsTaskContext
{
    protected function seedBaseReferences(): void
    {
        foreach (['executive', 'director', 'staff'] as $role) {
            Role::query()->firstOrCreate(['role' => $role]);
        }

        foreach (['Ready', 'Stand By', 'Not Ready', 'Absent'] as $status) {
            Statussdm::query()->firstOrCreate(['status_sdm' => $status]);
        }

        $statuses = [
            ['status' => 'To Do', 'class' => 'todo'],
            ['status' => 'In progress', 'class' => 'progress'],
            ['status' => 'Review', 'class' => 'review'],
            ['status' => 'Revision', 'class' => 'revision'],
            ['status' => 'Complete', 'class' => 'complete'],
        ];
        foreach ($statuses as $status) {
            StatusTask::query()->firstOrCreate(['class' => $status['class']], ['status' => $status['status']]);
        }

        $difficulties = [
            ['difficulty' => 'Low', 'class' => 'low'],
            ['difficulty' => 'Stand By', 'class' => 'standby'],
        ];
        foreach ($difficulties as $difficulty) {
            TaskDifficulty::query()->firstOrCreate(
                ['difficulty' => $difficulty['difficulty']],
                ['class' => $difficulty['class']]
            );
        }

        foreach ([['status' => 'Running', 'class' => 'running'], ['status' => 'Maintenance', 'class' => 'maintenance']] as $status) {
            StatusProject::query()->firstOrCreate(['class' => $status['class']], ['status' => $status['status']]);
        }
    }

    protected function makeUser(string $role, array $overrides = []): User
    {
        $division = Division::query()->firstOrCreate(['divisi' => 'Engineering']);
        $roleModel = Role::query()->where('role', $role)->firstOrFail();

        return User::query()->create(array_merge([
            'name' => ucfirst($role).' User '.fake()->unique()->numerify('###'),
            'nik' => fake()->unique()->numerify('00#########'),
            'email' => fake()->unique()->safeEmail(),
            'id_divisi' => $division->id,
            'id_role' => $roleModel->id,
            'password' => Hash::make('password'),
        ], $overrides));
    }

    protected function makeProject(User $director): Project
    {
        $runningStatus = StatusProject::query()->where('class', 'running')->firstOrFail();

        return Project::query()->create([
            'name' => 'Project '.fake()->unique()->word(),
            'start_date' => now()->subDay()->toDateString(),
            'end_date' => now()->addDays(30)->toDateString(),
            'id_status' => $runningStatus->id,
            'id_director' => $director->id,
        ]);
    }

    protected function makeTask(User $owner, Project $project, string $statusClass, ?string $difficultyName = 'Low'): Task
    {
        $status = StatusTask::query()->where('class', $statusClass)->firstOrFail();
        $difficulty = TaskDifficulty::query()->where('difficulty', $difficultyName)->firstOrFail();

        return Task::query()->create([
            'name' => 'Task '.fake()->unique()->word(),
            'id_user' => $owner->id,
            'id_project' => $project->id,
            'id_difficulty' => $difficulty->id,
            'id_status' => $status->id,
            'created_by' => $owner->id,
        ]);
    }
}
