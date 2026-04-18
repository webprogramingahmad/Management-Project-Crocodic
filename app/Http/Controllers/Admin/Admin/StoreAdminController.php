<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StoreAdminController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:6',
            'email' => 'required|email|unique:users,email',
            'id_divisi' => 'required|uuid|exists:divisions,id',
            'id_role' => 'required|uuid|exists:roles,id',
        ]);

        $notReady = Statussdm::firstOrCreate(['status_sdm' => 'Not Ready']);

        User::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'id_divisi' => $request->id_divisi,
            'id_role' => $request->id_role,
            'id_activity_status_sdm' => $notReady->id,
        ]);

        return redirect()->route('executive.accounts.index')->with('success', 'User created successfully.');
    }
}
