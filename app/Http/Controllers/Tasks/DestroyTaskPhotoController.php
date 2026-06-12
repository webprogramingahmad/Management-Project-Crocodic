<?php

namespace App\Http\Controllers\Tasks;

use App\Http\Controllers\Controller;
use App\Models\TaskPhoto;
use App\Support\TaskPhotoManager;
use App\Support\TaskRunningTimer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestroyTaskPhotoController extends Controller
{
    /**
     * Hapus satu foto bukti hasil kerja. Hanya pemilik task & selama task belum Review.
     */
    public function __invoke(Request $request, $photo)
    {
        $taskPhoto = TaskPhoto::with('task.status')->findOrFail($photo);

        $task = $taskPhoto->task;
        abort_if(! $task, 404);
        abort_if(TaskRunningTimer::isReviewStatus($task->status), 403);

        TaskPhotoManager::delete($taskPhoto, Auth::user());

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Foto berhasil dihapus.');
    }
}
