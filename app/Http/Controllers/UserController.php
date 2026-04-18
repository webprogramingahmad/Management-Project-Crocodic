<?php

namespace App\Http\Controllers;

use App\Models\Division;
use App\Models\Role;
use App\Models\Statussdm;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['division', 'role', 'statussdm'])->get();
        return view('admin', compact('users'));
    }

    public function create()
    {
        $divisions = Division::all();
        return view('admin-create', compact('divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'required|string|confirmed|min:6',
            'email' => 'required|email|unique:users,email',
            'id_divisi' => 'required|exists:divisions,id',
        ]);

        $role = Role::where('role', 'staff')->first();
        $notReady = Statussdm::firstOrCreate(['status_sdm' => 'Not Ready']);

        User::create([
            'id' => Str::uuid(),
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'id_divisi' => $request->id_divisi,
            'id_role' => $role?->id,
            'id_status_sdm' => null,
            'id_activity_status_sdm' => $notReady->id,
        ]);

        return redirect()->route('executive.accounts.index')->with('success', 'User created successfully.');
    }

    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $user->delete();

        return redirect()->back()->with('success', 'User berhasil dihapus.');
    }

    public function show(string $id)
    {
        $user = User::with('division', 'role')->findOrFail($id);
        $statussdms = Statussdm::all();
        return view('admin-detail', compact('user', 'statussdms'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'link_tele' => 'nullable|string|max:255',
            'no_telp' => 'nullable|string|max:15',
            'alamat' => 'nullable|string|max:255',
            'tgl_lahir' => 'nullable|date',
        ]);

        $user->update($validated);

        return back()->with('success', 'Profil berhasil diperbarui');
    }
}
