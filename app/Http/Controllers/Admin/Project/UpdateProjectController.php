<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectSdmAssignment;
use Illuminate\Http\Request;

class UpdateProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $project = Project::findOrFail($id);

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

        $project->update($validated);

        $project->sdms()->sync($sdmIds);

        return redirect()->route('executive.projects.index')->with('success', 'Project berhasil diupdate');
    }
}
