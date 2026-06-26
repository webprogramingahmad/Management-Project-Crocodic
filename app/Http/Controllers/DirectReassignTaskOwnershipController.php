<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Support\TaskOwnershipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DirectReassignTaskOwnershipController extends Controller
{
    public function __invoke(Request $request, string $id)
    {
        $request->validate([
            'to_user_id' => 'required|uuid|exists:users,id',
            'reason' => 'nullable|string|max:2000',
        ]);

        $task = Task::with(['status', 'project'])->findOrFail($id);

        TaskOwnershipManager::directReassign(
            Auth::user(),
            $task,
            $request->to_user_id,
            $request->input('reason')
        );

        return redirect()->back()->with('success', 'Kepemilikan task berhasil dialihkan.');
    }
}
