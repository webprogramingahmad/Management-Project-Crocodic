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
        $difficulties = ProjectDifficulty::latest()->get();
        $directors = User::whereHas('role', fn($q) => $q->where('role', 'director'))->orderBy('name', 'asc')->get();
        $divisions = Division::with('users')->orderBy('divisi', 'asc')->get();
        $users = User::select('id', 'name', 'id_divisi')->orderBy('name', 'asc')->get();

        return view('view.projects.edit', compact('project', 'difficulties', 'statusprojects', 'directors', 'divisions', 'users'));
    }
}
