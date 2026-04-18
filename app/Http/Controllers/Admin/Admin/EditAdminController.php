<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\LastGraduate;
use App\Models\Statussdm;
use App\Models\User;

class EditAdminController extends Controller
{
    public function __invoke($id)
    {
        $user = User::with('division', 'role', 'statussdm')->findOrFail($id);
        $statussdms = Statussdm::forEmploymentProfileSelect();
        $lastgraduates = LastGraduate::orderBy('graduate', 'asc')->get();

        return view('view.admin.edit', compact('user', 'statussdms', 'lastgraduates'));
    }
}
