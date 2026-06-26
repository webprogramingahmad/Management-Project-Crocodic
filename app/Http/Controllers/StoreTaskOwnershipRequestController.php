<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Support\TaskOwnershipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreTaskOwnershipRequestController extends Controller
{
    public function __invoke(Request $request, string $id)
    {
        $request->validate([
            'to_user_id' => 'required|uuid|exists:users,id',
            'reason' => 'required|string|max:2000',
        ]);

        $task = Task::with(['status', 'project'])->findOrFail($id);

        TaskOwnershipManager::submitRequest(
            Auth::user(),
            $task,
            $request->to_user_id,
            $request->reason
        );

        return redirect()->back()->with('success', 'Pengajuan alih kepemilikan task berhasil dikirim.');
    }
}
