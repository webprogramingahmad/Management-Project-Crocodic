<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Models\StatusAdministration;
use App\Models\StatusTask;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class IndexActivityController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $month = $request->get('month', now()->month);
        $year = $request->get('year', now()->year);

        $acceptedStatus = StatusAdministration::where('name', 'accept')->first();
        $completeStatus = StatusTask::firstByClass('complete');

        $users = User::with(['administrations', 'tasks', 'projects', 'division'])->orderBy('name', 'asc')->get();

        $users->map(function ($user) use ($acceptedStatus, $completeStatus, $month, $year) {
            $user->accepted_absent_count = $user->administrations
                ->where('id_status', $acceptedStatus?->id)
                ->filter(function ($item) use ($month, $year) {
                    return Carbon::parse($item->start_date)->month == $month &&
                        Carbon::parse($item->start_date)->year == $year;
                })
                ->count();

            $user->completed_task_count = $user->tasks
                ->where('id_status', $completeStatus?->id)
                ->filter(function ($item) use ($month, $year) {
                    return Carbon::parse($item->updated_at)->month == $month &&
                        Carbon::parse($item->updated_at)->year == $year;
                })
                ->count();

            $user->projects_joined_count = $user->projects
                ->filter(function ($project) use ($month, $year) {
                    return Carbon::parse($project->start_date)->year <= $year &&
                        Carbon::parse($project->end_date)->year >= $year &&
                        Carbon::parse($project->start_date)->month <= $month &&
                        Carbon::parse($project->end_date)->month >= $month;
                })
                ->count();

            return $user;
        });

        return view('view.activity.index', compact('users', 'month', 'year'));
    }
}
