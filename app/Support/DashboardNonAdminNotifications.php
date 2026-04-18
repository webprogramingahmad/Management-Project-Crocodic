<?php

namespace App\Support;

use App\Models\Administration;
use App\Models\Project;
use App\Models\StatusAdministration;
use App\Models\User;

class DashboardNonAdminNotifications
{
    /**
     * Feed kanan dashboard (director/user): izin pending (milik user) + project completed (30 hari), sama pola dengan admin.
     *
     * @return array{dashboardNotifications: \Illuminate\Support\Collection, dashboardNotificationBadgeCount: int}
     */
    public static function feed(User $user): array
    {
        $pendingStatusId = StatusAdministration::where('name', 'pending')->value('id');
        $pendingAdministrations = collect();
        if ($pendingStatusId) {
            $pendingAdministrations = Administration::query()
                ->with(['user.division', 'category', 'status'])
                ->where('id_user', $user->id)
                ->where('id_status', $pendingStatusId)
                ->orderByDesc('created_at')
                ->limit(25)
                ->get();
        }

        $completedNotifyDays = 30;
        $recentCompletedProjects = Project::query()
            ->with(['status', 'difficulty', 'director'])
            ->whereHas('status', function ($q) {
                $q->where('class', 'completed');
            })
            ->where('updated_at', '>=', now()->subDays($completedNotifyDays))
            ->where(function ($q) use ($user) {
                $q->where('id_director', $user->id)
                    ->orWhereHas('sdms', function ($qq) use ($user) {
                        $qq->where('users.id', $user->id);
                    });
            })
            ->orderByDesc('updated_at')
            ->limit(25)
            ->get();

        $dashboardNotifications = collect();
        foreach ($pendingAdministrations as $adm) {
            $dashboardNotifications->push((object) [
                'kind' => 'administration',
                'sort_at' => $adm->created_at,
                'administration' => $adm,
            ]);
        }
        foreach ($recentCompletedProjects as $project) {
            $dashboardNotifications->push((object) [
                'kind' => 'project_completed',
                'sort_at' => $project->updated_at,
                'project' => $project,
            ]);
        }
        $dashboardNotifications = $dashboardNotifications
            ->sortByDesc(fn ($n) => $n->sort_at?->timestamp ?? 0)
            ->take(35)
            ->values();

        $dashboardNotificationBadgeCount = $pendingAdministrations->count() + $recentCompletedProjects->count();

        return [
            'dashboardNotifications' => $dashboardNotifications,
            'dashboardNotificationBadgeCount' => $dashboardNotificationBadgeCount,
        ];
    }
}
