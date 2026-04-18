<?php

namespace App\Http\Controllers\User\Projects;

use App\Http\Controllers\Controller;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexProjectController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $user = Auth::user();
        $level = $request->query('level');
        $status = $request->query('status');
        $search = $request->query('search');

        $query = Project::with(['tasks', 'difficulty', 'status'])
            ->whereHas('sdms', function ($q) use ($user) {
                $q->where('id', $user->id);
            })
            ->orderBy('name', 'asc');

        if ($level) {
            $query->whereHas('difficulty', function ($q) use ($level) {
                $q->whereRaw('LOWER(difficulty) = ?', [strtolower($level)]);
            });
        }

        if ($status) {
            $query->whereHas('status', function ($q) use ($status) {
                $q->whereRaw('LOWER(status) = ?', [strtolower($status)]);
            });
        }

        if ($search) {
            $query->where('name', 'like', "%$search%");
        }

        $projects = $query->get();

        return view('view.projects.index', compact('projects'));
    }
}
