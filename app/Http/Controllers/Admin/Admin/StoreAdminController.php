<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreAdminController extends Controller
{
    public function __invoke(Request $request)
    {

        $request->merge([
            'id_divisi' => $request->input('id_divisi') ?: null,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:6',
            'email' => 'required|email|unique:users,email',
            'nik' => 'required|string|max:20|unique:users,nik',
            'tgl_masuk' => 'required|date',
            'id_status_sdm' => ['required', 'uuid', Rule::in(Statussdm::employmentTypeIds())],
            'id_divisi' => [
                'nullable',
                'uuid',
                'exists:divisions,id',
                Rule::requiredIf(function () use ($request) {
                    $role = Role::query()->find($request->id_role);

                    return $role && $role->role === 'staff';
                }),
            ],
            'id_role' => 'required|uuid|exists:roles,id',
        ]);

        $role = Role::query()->findOrFail($request->id_role);
        $idDivisi = $role->role === 'staff' ? $request->id_divisi : null;

        $notReady = Statussdm::firstOrCreate(['status_sdm' => 'Not Ready']);

        User::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'nik' => $request->nik,
            'tgl_masuk' => $request->tgl_masuk,
            'id_status_sdm' => $request->id_status_sdm,
            'id_divisi' => $idDivisi,
            'id_role' => $request->id_role,
            'id_activity_status_sdm' => $notReady->id,
        ]);

        return redirect()->route('executive.accounts.index')->with('success', 'User created successfully.');
    }
}
