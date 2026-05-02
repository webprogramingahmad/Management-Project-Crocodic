<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Role;
use App\Models\Statussdm;

class CreateAdminController extends Controller
{
    public function __invoke()
    {
        $divisions = Division::orderBy('divisi', 'asc')->get();
        $roles = Role::orderBy('role', 'asc')->get();
        $employmentStatuses = Statussdm::forEmploymentProfileSelect();

        return view('view.admin.create', compact('divisions', 'roles', 'employmentStatuses'));
    }
}
