<?php

namespace App\Http\Controllers\Director\Profile;

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

        // Hitung project sebagai anggota SDM + project yang dipimpin sebagai director (unik per ID),
        // selaras dengan metrik di halaman Activity agar tidak ada perbedaan data.
        $projects_joined_count = $user->projects()->pluck('projects.id')
            ->merge($user->directedProjects()->pluck('id'))
            ->unique()
            ->count();

        return view('view.profile.index', compact('user', 'accepted_absent_count', 'completed_task_count', 'projects_joined_count'));
    }
}
