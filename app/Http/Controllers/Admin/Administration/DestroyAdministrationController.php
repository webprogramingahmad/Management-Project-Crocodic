<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;

class DestroyAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $administration = Administration::findOrFail($id);
        $administration->delete();

        return redirect()->route('executive.administration.index')->with('success', 'Project berhasil dihapus.');
    }
}
