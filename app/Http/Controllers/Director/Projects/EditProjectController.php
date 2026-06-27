<?php

namespace App\Http\Controllers\Director\Projects;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Project;
use App\Models\ProjectDifficulty;
use App\Models\StatusProject;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EditProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $project = Project::with('sdms.division')->findOrFail($id);

        abort_unless($project->id_director === Auth::id(), 403);

        $statusprojects = StatusProject::orderBy('status', 'asc')->get();
        $statusOrder = ['todo' => 1, 'running' => 2, 'maintenance' => 3, 'completed' => 4];
        $currentOrder = $statusOrder[strtolower((string) ($project->status?->class ?? ''))] ?? null;
        $allowedStatusProjects = $statusprojects->filter(function (StatusProject $status) use ($currentOrder, $statusOrder) {
            if ($currentOrder === null) {
                return true;
            }
            $candidateOrder = $statusOrder[strtolower((string) ($status->class ?? ''))] ?? null;
            if ($candidateOrder === null) {
                return true;
            }

            return in_array($candidateOrder, [$currentOrder, $currentOrder + 1], true);
        })->values();
        $difficulties = ProjectDifficulty::latest()->get();
        $directors = User::whereHas('role', fn($q) => $q->where('role', 'director'))->orderBy('name', 'asc')->get();
        $divisions = Division::with('users')->orderBy('divisi', 'asc')->get();
        $users = User::select('id', 'name', 'id_divisi')->staffRole()->orderBy('name', 'asc')->get();

        return view('view.projects.edit', compact('project', 'difficulties', 'statusprojects', 'allowedStatusProjects', 'directors', 'divisions', 'users'));
    }
}
