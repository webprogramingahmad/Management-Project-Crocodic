<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Controller;
use App\Models\Administration;

class IndexAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $administrations = Administration::with(['user', 'status', 'category'])->latest()->get();

        return view('view.administration.index', compact('administrations'));
    }
}
