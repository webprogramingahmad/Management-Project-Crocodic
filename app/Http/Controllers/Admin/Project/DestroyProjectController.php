<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;

class DestroyProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $project = Project::findOrFail($id);
        $project->sdms()->detach();
        $project->delete();

        return redirect()->route('executive.projects.index')->with('success', 'Project berhasil dihapus.');
    }
}
