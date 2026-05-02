<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\StatusAdministration;
use App\Models\StatusTask;


class IndexProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $user = Auth::user();
        $acceptedStatus = StatusAdministration::where('name', 'accept')->first();
        $completeStatus = StatusTask::firstByClass('complete');

        $accepted_absent_count = $user->administrations()
            ->where('id_status', $acceptedStatus?->id)
            ->count();

        $completed_task_count = $user->tasks()
            ->where('id_status', $completeStatus?->id)
            ->count();

        $projects_joined_count = $user->projects()->count();

        return view('view.profile.index', compact('user', 'accepted_absent_count', 'completed_task_count', 'projects_joined_count'));
    }
}
