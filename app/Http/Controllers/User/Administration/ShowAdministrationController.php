<?php

namespace App\Http\Controllers\User\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use Illuminate\Support\Facades\Auth;

class ShowAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke($id)
    {
        $administration = Administration::with(['user.role', 'category', 'status'])->findOrFail($id);
        abort_unless($administration->id_user === Auth::id(), 403);

        return view('view.administration.view', compact('administration'));
    }
}
