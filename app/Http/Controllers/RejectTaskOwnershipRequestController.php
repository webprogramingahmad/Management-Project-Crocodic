<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskOwnershipTransferRequest;
use App\Support\TaskOwnershipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RejectTaskOwnershipRequestController extends Controller
{
    public function __invoke(Request $request, string $id, string $requestId)
    {
        $request->validate([
            'review_note' => 'nullable|string|max:1000',
        ]);

        $task = Task::with(['status', 'project'])->findOrFail($id);
        $transferRequest = TaskOwnershipTransferRequest::query()
            ->where('id_task', $task->id)
            ->findOrFail($requestId);

        TaskOwnershipManager::rejectRequest(
            Auth::user(),
            $transferRequest,
            $request->input('review_note')
        );

        return redirect()->back()->with('success', 'Pengajuan alih kepemilikan ditolak.');
    }
}
