<?php

namespace App\Http\Controllers\Admin\Tasks;

use App\Http\Controllers\Controller;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\StatusSdmManager;

class UpdateTaskController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        abort_if(Auth::user()->role?->role === 'executive', 403);

        $task = Task::findOrFail($request->id);

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

        return redirect()->route('executive.tasks.index')->with('success', 'Task updated successfully.');
    }
}
