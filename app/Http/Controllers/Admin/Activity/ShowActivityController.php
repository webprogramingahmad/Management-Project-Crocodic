<?php

namespace App\Http\Controllers\Admin\Activity;

use App\Http\Controllers\Controller;
use App\Models\StatusAdministration;
use App\Models\StatusTask;
use App\Models\User;
use App\Support\ActivityMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShowActivityController extends Controller
{
    /**
     * Tampilkan detail kinerja satu SDM (untuk executive & director).
     */
    public function __invoke(Request $request, string $id)
    {
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $authUser = Auth::user();
        $role = $authUser->role?->role;

        $user = User::with(ActivityMetrics::EAGER_RELATIONS)->findOrFail($id);

        // Otorisasi scope:
        // - executive : boleh melihat siapa pun
        // - director  : hanya SDM pada project yang ia pimpin atau dirinya sendiri
        if ($role === 'director') {
            $isSelf = (string) $user->id === (string) $authUser->id;
            $inHisProject = $user->projects
                ->contains(fn ($project) => (string) $project->id_director === (string) $authUser->id);

            abort_unless($isSelf || $inHisProject, 403);
        } elseif ($role !== 'executive') {
            abort(403);
        }

        $acceptedStatus = StatusAdministration::where('name', 'accept')->first();
        $completeStatus = StatusTask::firstByClass('complete');

        ActivityMetrics::decorate($user, $month, $year, $acceptedStatus, $completeStatus);

        $backRoute = $role === 'director'
            ? route('director.activity.index', ['month' => $month, 'year' => $year])
            : route('executive.activity.index', ['month' => $month, 'year' => $year]);

        return view('view.activity.show', [
            'me' => $user,
            'month' => $month,
            'year' => $year,
            'backRoute' => $backRoute,
        ]);
    }
}
