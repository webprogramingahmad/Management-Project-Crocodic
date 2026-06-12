<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Models\StatusAdministration;
use App\Models\StatusTask;
use App\Models\User;
use App\Support\ActivityMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexActivityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $acceptedStatus = StatusAdministration::where('name', 'accept')->first();
        $completeStatus = StatusTask::firstByClass('complete');

        $authUser = Auth::user();
        $role = $authUser->role?->role;

        // Scope data sesuai wewenang:
        // - executive : semua SDM
        // - director  : SDM pada project yang ia pimpin + dirinya sendiri
        // - staff     : hanya dirinya sendiri (self evaluation)
        $viewMode = $role === 'staff' ? 'self' : 'team';

        $usersQuery = User::with(ActivityMetrics::EAGER_RELATIONS)
            ->orderBy('name', 'asc');

        if ($role === 'director') {
            $directorId = $authUser->id;
            $usersQuery->where(function ($q) use ($directorId) {
                $q->whereHas('projects', fn ($p) => $p->where('id_director', $directorId))
                    ->orWhere('id', $directorId);
            });
        } elseif ($role === 'staff') {
            $usersQuery->where('id', $authUser->id);
        }

        $users = $usersQuery->get();

        $users->each(function ($user) use ($acceptedStatus, $completeStatus, $month, $year) {
            ActivityMetrics::decorate($user, $month, $year, $acceptedStatus, $completeStatus);
        });

        return view('view.activity.index', compact('users', 'month', 'year', 'viewMode'));
    }
}
