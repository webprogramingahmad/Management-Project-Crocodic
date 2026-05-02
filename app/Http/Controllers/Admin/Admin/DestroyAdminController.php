<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusTask;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DestroyAdminController extends Controller
{
    public function __invoke($id)
    {
        $user = User::findOrFail($id);

        if ((string) Auth::id() === (string) $user->id) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        if (($user->role->role ?? null) === 'executive') {
            return redirect()->back()->with('error', 'Executive account cannot be deleted from this page.');
        }

        $hasDirectedProjects = Project::query()
            ->where('id_director', $user->id)
            ->exists();
        if ($hasDirectedProjects) {
            return redirect()->back()->with('error', 'Cannot delete account: user is still assigned as a project director.');
        }

        $hasIncompleteTasks = Task::query()
            ->where('id_user', $user->id)
            ->whereHas('status', function ($q) {
                $q->where(function ($w) {
                    $w->where('class', '!=', 'complete');
                    $completeLabels = StatusTask::legacyLabelsForClass('complete');
                    if ($completeLabels !== []) {
                        $w->orWhereNotIn('status', $completeLabels);
                    }
                });
            })
            ->exists();
        if ($hasIncompleteTasks) {
            return redirect()->back()->with('error', 'Cannot delete account: user still has active/incomplete tasks.');
        }

        $user->delete();

        return redirect()->back()->with('success', 'Account deleted successfully.');
    }
}
