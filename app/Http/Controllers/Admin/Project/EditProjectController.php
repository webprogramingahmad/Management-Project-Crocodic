<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Project;
use App\Models\ProjectDifficulty;
use App\Models\StatusProject;
use App\Models\User;
use Illuminate\Http\Request;

class EditProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $project = Project::with('sdms.division')->findOrFail($id);
        $statusprojects = StatusProject::orderBy('status', 'asc')->get();
        $difficulties = ProjectDifficulty::latest()->get();
        $directors = User::whereHas('role', fn($q) => $q->where('role', 'director'))->orderBy('name', 'asc')->get();
        $divisions = Division::with('users')->orderBy('divisi', 'asc')->get();
        $users = User::select('id', 'name', 'id_divisi')->orderBy('name', 'asc')->get();

        return view('view.projects.edit', compact('project', 'difficulties', 'statusprojects', 'directors', 'divisions', 'users'));
    }
}
