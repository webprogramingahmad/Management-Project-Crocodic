<?php

namespace App\Http\Controllers\Admin\Project;

use App\Models\ProjectDifficulty;
use App\Models\StatusProject;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Support\ProjectWorkload;
use Illuminate\Http\Request;

class CreateProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $statusprojects = StatusProject::orderBy('status', 'asc')->get();
        $directors = User::whereHas('role', function ($q) {
            $q->where('role', 'director');
        })->orderBy('name', 'asc')->get();
        $difficulties = ProjectDifficulty::latest()->get();
        $divisions = Division::orderBy('divisi', 'asc')->get();
        $users = User::select('id', 'name', 'id_divisi')->orderBy('name', 'asc')->get();

        $workloadIds = $directors->pluck('id')->merge($users->pluck('id'))->unique()->values()->all();
        $workloadByUserId = ProjectWorkload::statsMapForUserIds($workloadIds);

        $usersForProjectJs = $users->map(function (User $u) use ($workloadByUserId) {
            $w = $workloadByUserId[$u->id] ?? ['count' => 0, 'max_days' => 0];

            return [
                'id' => $u->id,
                'name' => $u->name,
                'id_divisi' => $u->id_divisi,
                'workload_count' => $w['count'],
                'workload_max_days' => $w['max_days'],
            ];
        })->values();

        return view('view.projects.create', compact('statusprojects', 'directors', 'divisions', 'users', 'difficulties', 'workloadByUserId', 'usersForProjectJs'));
    }
}
