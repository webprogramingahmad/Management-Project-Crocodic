<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use Illuminate\Http\Request;

class UpdateAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $id)
    {
        $request->validate([
            'id_status' => 'required|uuid|exists:status_administrations,id',
        ]);

        $administration = Administration::findOrFail($id);
        $administration->id_status = $request->id_status;
        $administration->save();

        return redirect()->route('executive.administration.index')->with('success', 'Status absen berhasil diubah');
    }
}
