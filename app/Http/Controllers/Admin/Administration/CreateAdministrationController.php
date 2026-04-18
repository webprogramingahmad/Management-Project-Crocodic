<?php

namespace App\Http\Controllers\Admin\Administration;

use App\Http\Controllers\Controller;
use App\Models\CategoryAdministration;
use App\Models\StatusAdministration;

class CreateAdministrationController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke()
    {
        $categories = CategoryAdministration::orderBy('name', 'asc')->get();

        return view('view.administration.create', compact('categories'));
    }
}
