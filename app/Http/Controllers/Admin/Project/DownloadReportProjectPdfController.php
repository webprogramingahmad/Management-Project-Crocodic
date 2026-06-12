<?php

namespace App\Http\Controllers\Admin\Project;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\ProjectReportAccess;
use App\Support\ProjectReportBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DownloadReportProjectPdfController extends Controller
{
    /**
     * Unduh Project Report sebagai PDF (lengkap: ringkasan + detail task + evidence + revisi).
     */
    public function __invoke(Request $request, string $id)
    {
        $actor = Auth::user();
        $role = $actor->role?->role;

        abort_unless(in_array($role, ['executive', 'director'], true), 403);

        $project = Project::with(['difficulty', 'status', 'director', 'sdms.division'])
            ->findOrFail($id);

        ProjectReportAccess::assertCanView($actor, $project);

        $report = ProjectReportBuilder::build($project, forPdf: true);
        $generatedAt = now();

        $pdf = Pdf::loadView('view.projects.report-pdf', array_merge($report, [
            'generatedAt' => $generatedAt,
        ]))->setPaper('a4', 'portrait');

        $filename = Str::slug($project->name) . '-project-report-' . $generatedAt->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
