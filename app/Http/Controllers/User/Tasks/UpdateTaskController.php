<?php

namespace App\Http\Controllers\User\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\StatusSdmManager;
use App\Support\TaskBoardAccess;

class UpdateTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $task = Task::findOrFail($request->id);

        TaskBoardAccess::assertCanActOnTaskForBoard(Auth::user(), $task);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'id_difficulty' => 'required|uuid|exists:task_difficulties,id',
            'description' => 'nullable|string|max:5000',
        ]);

        $validated['description'] = ($validated['description'] ?? '') !== ''
            ? trim((string) $validated['description'])
            : null;

        $task->update($validated);

        if ($task->user) {
            StatusSdmManager::syncForUser($task->user);
        }

        return redirect()->route('staff.tasks.index')->with('success', 'Task updated successfully.');
    }
}
