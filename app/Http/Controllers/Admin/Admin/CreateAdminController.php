<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\Role;

class CreateAdminController extends Controller
{
    public function __invoke()
    {
        $divisions = Division::orderBy('divisi', 'asc')->get();
        $roles = Role::orderBy('role', 'asc')->get();
        return view('view.admin.create', compact('divisions', 'roles'));
    }
}
