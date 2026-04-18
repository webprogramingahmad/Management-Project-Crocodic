<?php

namespace App\Http\Controllers\Director\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;
use Illuminate\Support\Facades\Auth;

class IndexAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $user = Auth::user();
        $administrations = Administration::with(['user', 'status', 'category'])
            ->where('id_user', $user->id)
            ->latest()
            ->get();

        return view('view.administration.index', compact('administrations', 'user'));
    }
}
