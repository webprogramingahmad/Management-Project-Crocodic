<?php

namespace App\Support;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;

/**
 * Otorisasi task board (drag status, edit task global, create/transfer dengan id_project).
 */
class TaskBoardAccess
{
    public static function canActOnTaskForBoard(User $user, Task $task): bool
    {
        $user->loadMissing('role');
        $roleKey = $user->role?->role;

        if ($roleKey === 'executive') {
            return true;
        }

        if ((string) $task->id_user === (string) $user->id) {
            return true;
        }

        if (! $task->id_project) {
            return false;
        }

        $task->loadMissing('project');
        $project = $task->project;
        if (! $project) {
            return false;
        }

        if ($roleKey === 'director') {
            if ((string) $project->id_director === (string) $user->id) {
                return true;
            }

            return $project->sdms()->where('users.id', $user->id)->exists();
        }

        if ($roleKey === 'staff') {
            return $project->sdms()->where('users.id', $user->id)->exists();
        }

        return false;
    }

    public static function assertCanActOnTaskForBoard(User $user, Task $task): void
    {
        abort_unless(self::canActOnTaskForBoard($user, $task), 403);
    }

    /**
     * Create / transfer task global: user harus berhak atas proyek (director pemilik atau SDM; staff = SDM).
     */
    public static function assertCanUseProjectForTaskMutation(User $user, string $projectId): void
    {
        $user->loadMissing('role');
        $roleKey = $user->role?->role;

        if ($roleKey === 'executive') {
            return;
        }

        $project = Project::query()->findOrFail($projectId);

        if ($roleKey === 'director') {
            $ok = (string) $project->id_director === (string) $user->id
                || $project->sdms()->where('users.id', $user->id)->exists();
            abort_unless($ok, 403);

            return;
        }

        if ($roleKey === 'staff') {
            abort_unless($project->sdms()->where('users.id', $user->id)->exists(), 403);

            return;
        }

        abort(403);
    }
}
