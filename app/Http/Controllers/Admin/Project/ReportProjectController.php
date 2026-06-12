<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectReportAccess;
use App\Support\ProjectReportBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportProjectController extends Controller
{
    /**
     * Project Report: ringkasan task & kontribusi anggota per project.
     */
    public function __invoke(Request $request, string $id)
    {
        $actor = Auth::user();
        $role = $actor->role?->role;

        abort_unless(in_array($role, ['executive', 'director'], true), 403);

        $project = Project::with(['difficulty', 'status', 'director', 'sdms.division'])
            ->findOrFail($id);

        ProjectReportAccess::assertCanView($actor, $project);

        $report = ProjectReportBuilder::build($project);

        $reportRoute = ProjectReportAccess::reportRouteName($role);
        $reportTaskRoute = ProjectReportAccess::reportTaskRouteName($role);
        $reportPdfRoute = ProjectReportAccess::reportPdfRouteName($role);
        $backRoute = ProjectReportAccess::projectsIndexRouteName($role);

        return view('view.projects.report', array_merge($report, compact(
            'reportRoute',
            'reportTaskRoute',
            'reportPdfRoute',
            'backRoute',
        )));
    }
}
