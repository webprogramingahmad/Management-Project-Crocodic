<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use App\Models\StatusAdministration;

class ShowAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $administration = Administration::with(['user.role', 'category', 'status'])->findOrFail($id);

        $statusAccept = StatusAdministration::where('name', 'accept')->first();
        $statusReject = StatusAdministration::where('name', 'reject')->first();
        $idAccept = $statusAccept->id;
        $idReject = $statusReject->id;

        return view('view.administration.view', compact('administration', 'idAccept', 'idReject'));
    }
}
