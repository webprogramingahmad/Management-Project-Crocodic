<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\StatusProject;
use App\Support\ProjectSdmAssignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoreProjectController extends Controller
{
    public function __invoke(Request $request)
    {
        $validated = $request->validate([
            'name'            => ['required', 'string', 'max:255'],
            'start_date'      => ['required', 'date'],
            'end_date'        => ['required', 'date', 'after_or_equal:start_date'],
            'id_difficulty'   => ['nullable', 'uuid', 'exists:project_difficulties,id'],
            'description'     => ['nullable', 'string'],
            'id_director'     => ['required', 'uuid', 'exists:users,id'],
        ]);

        $todoStatus = StatusProject::firstByClass('todo');
        abort_unless($todoStatus, 422, 'Status project "To do" tidak ditemukan.');

        $sdmIds = ProjectSdmAssignment::validatedIds($request);

        DB::transaction(function () use ($validated, $sdmIds, $todoStatus) {
            $project = Project::create([
                'name'            => $validated['name'],
                'start_date'      => $validated['start_date'],
                'end_date'        => $validated['end_date'],
                'id_difficulty'   => $validated['id_difficulty'] ?? null,
                'id_status'       => $todoStatus->id,
                'description'     => $validated['description'] ?? null,
                'id_director'     => $validated['id_director'],
            ]);

            if ($sdmIds !== []) {
                $project->sdms()->attach($sdmIds);
            }
        });

        return redirect()->route('executive.projects.index')->with('success', 'Project berhasil dibuat.');
    }
}
