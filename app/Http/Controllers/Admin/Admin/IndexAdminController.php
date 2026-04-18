<?php

namespace App\Http\Controllers\Admin\Admin;

use App\Http\Controllers\Controller;
use App\Models\Division;
use App\Models\User;
use Illuminate\Http\Request;

class IndexAdminController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->only(['search']);
        $divisionId = $request->division_id;

        $query = User::with(['division', 'role'])
            ->filter($filters)
            ->when($divisionId, function ($q) use ($divisionId) {
                $q->where('id_divisi', $divisionId);
            })
            ->orderBy('name', 'asc')
            ->get();

        $users = $query;

        $divisions = Division::orderBy('divisi', 'asc')->get();

        $selectedDivision = $divisionId ? Division::find($divisionId) : null;

        return view('view.admin.index', compact('users', 'divisions', 'selectedDivision', 'filters'));
    }
}
