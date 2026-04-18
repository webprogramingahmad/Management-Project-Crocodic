<?php

namespace App\Http\Controllers\Director\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectSdmAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UpdateProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::findOrFail($id);

        abort_unless($project->id_director === Auth::id(), 403);

        $validated = $request->validate([
            'name'          => 'required|string|max:255',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'id_difficulty' => ['nullable', 'uuid', 'exists:project_difficulties,id'],
            'id_status'     => 'required|uuid|exists:status_projects,id',
            'description'   => 'required|string',
            'id_director'   => 'required|uuid|exists:users,id',
        ]);

        $sdmIds = ProjectSdmAssignment::validatedIds($request);

        $validated['id_director'] = Auth::id();

        $project->update($validated);

        $project->sdms()->sync($sdmIds);

        return redirect()->route('director.projects.index')->with('success', 'Project berhasil diupdate');
    }
}
