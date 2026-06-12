<?php

namespace App\Support;

use App\Models\Project;
use App\Models\User;

class ProjectReportAccess
{
    public static function assertCanView(User $actor, Project $project): void
    {
        $role = $actor->role?->role;

        if ($role === 'executive') {
            return;
        }

        if ($role === 'director' && (string) $project->id_director === (string) $actor->id) {
            return;
        }

        abort(403);
    }

    public static function reportRouteName(?string $role): string
    {
        return $role === 'director' ? 'director.project.report' : 'executive.project.report';
    }

    public static function reportTaskRouteName(?string $role): string
    {
        return $role === 'director' ? 'director.project.report.task' : 'executive.project.report.task';
    }

    public static function reportPdfRouteName(?string $role): string
    {
        return $role === 'director' ? 'director.project.report.pdf' : 'executive.project.report.pdf';
    }

    public static function projectsIndexRouteName(?string $role): string
    {
        return $role === 'director' ? 'director.projects.index' : 'executive.projects.index';
    }
}
