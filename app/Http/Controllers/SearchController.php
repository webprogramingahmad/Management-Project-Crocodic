<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        // Logika pencarian, misalnya
        $query = $request->input('query');
        // Kembalikan view atau data
        return view('search.results', compact('query'));
    }
}