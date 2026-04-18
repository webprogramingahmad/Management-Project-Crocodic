<?php

namespace App\Http\Controllers\Director\Profile;

use App\Http\Controllers\Controller;
use App\Models\LastGraduate;
use App\Models\Statussdm;
use App\Models\User;

class EditProfileController extends Controller
{
    /**
     * Show the edit profile form.
     */
    public function __invoke($id)
    {
        $user = User::with(['division', 'role', 'statussdm', 'activityStatussdm'])->findOrFail($id);
        $statussdms = Statussdm::forEmploymentProfileSelect();
        $lastgraduates = LastGraduate::orderBy('graduate', 'asc')->get();
        $role = $user->role->role;
        $sdmOperationalLocked = Statussdm::isOperationalWorkflow($user->activityStatussdm);

        return view('view.profile.edit', compact('user', 'statussdms', 'lastgraduates', 'role', 'sdmOperationalLocked'));
    }
}
