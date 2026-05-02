<?php

namespace App\Http\Controllers\Director\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusProject;
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

        $project->loadMissing('status');
        $incomingStatus = StatusProject::findOrFail($validated['id_status']);

        $this->validateStatusTransition($project->status, $incomingStatus);

        $validated['id_director'] = Auth::id();

        $project->update($validated);

        $project->sdms()->sync($sdmIds);

        return redirect()->route('director.projects.index')->with('success', 'Project berhasil diupdate');
    }

    private function validateStatusTransition(?StatusProject $current, StatusProject $incoming): void
    {
        $order = [
            'todo' => 1,
            'running' => 2,
            'maintenance' => 3,
            'completed' => 4,
        ];

        $currentClass = strtolower((string) ($current?->class ?? ''));
        $incomingClass = strtolower((string) ($incoming->class ?? ''));

        if (!isset($order[$currentClass]) || !isset($order[$incomingClass])) {
            return;
        }

        if ($incomingClass === $currentClass) {
            return;
        }

        if ($order[$incomingClass] !== ($order[$currentClass] + 1)) {
            abort(422, 'Status project harus berurutan: To do -> Running -> Maintenance -> Complete.');
        }
    }
}
