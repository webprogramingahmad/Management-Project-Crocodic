<?php

namespace App\Http\Controllers\User\Profile;

use App\Http\Controllers\Controller;
use App\Models\LastGraduate;
use App\Models\Statussdm;
use App\Models\User;

class EditProfileController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $user = User::with('division', 'role')->findOrFail($id);
        $statussdms = Statussdm::forEmploymentProfileSelect();
        $lastgraduates = LastGraduate::orderBy('graduate', 'asc')->get();
        $role = $user->role->role;

        return view('view.profile.edit', compact('user', 'statussdms', 'lastgraduates', 'role'));
    }
}
