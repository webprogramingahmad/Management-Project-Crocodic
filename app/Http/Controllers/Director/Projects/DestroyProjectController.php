<?php

namespace App\Http\Controllers\Director\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DestroyProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $project = Project::findOrFail($id);

        abort_unless($project->id_director === Auth::id(), 403);

        $project->sdms()->detach();
        $project->delete();

        return redirect()->route('director.projects.index')->with('success', 'Project berhasil dihapus.');
    }
}
