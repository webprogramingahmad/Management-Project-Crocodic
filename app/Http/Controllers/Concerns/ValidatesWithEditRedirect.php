<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

trait ValidatesWithEditRedirect
{
    /**
     * Validasi dengan redirect eksplisit ke halaman edit + old input (password tidak di-flash).
     * Mengembalikan RedirectResponse agar session flash di-commit seperti redirect biasa
     * (lebih andal daripada HttpResponseException di beberapa alur).
     *
     * @param  array<string, mixed>  $rules
     * @return array<string, mixed>|RedirectResponse
     */
    protected function validateWithEditRedirect(Request $request, array $rules, string $editRouteName, mixed $routeKey): array|RedirectResponse
    {
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->route($editRouteName, $routeKey)
                ->withInput($request->except(['password', 'password_confirmation', 'current_password']))
                ->withErrors($validator);
        }

        return $validator->validated();
    }
}
