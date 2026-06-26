<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskOwnershipTransferRequest;
use App\Support\TaskOwnershipManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ApproveTaskOwnershipRequestController extends Controller
{
    public function __invoke(Request $request, string $id, string $requestId)
    {
        $request->validate([
            'to_user_id' => 'nullable|uuid|exists:users,id',
            'review_note' => 'nullable|string|max:1000',
        ]);

        $task = Task::with(['status', 'project'])->findOrFail($id);
        $transferRequest = TaskOwnershipTransferRequest::query()
            ->where('id_task', $task->id)
            ->findOrFail($requestId);

        TaskOwnershipManager::approveRequest(
            Auth::user(),
            $transferRequest,
            $request->input('to_user_id'),
            $request->input('review_note')
        );

        return redirect()->back()->with('success', 'Pengajuan alih kepemilikan disetujui.');
    }
}
