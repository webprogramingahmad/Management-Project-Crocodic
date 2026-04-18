<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::user() && Auth::user()->role->role !== 'executive') {
            return match (Auth::user()->role->role) {
                'staff' => redirect()->route('staff.dashboard.index'),
                'director' => redirect()->route('director.dashboard.index'),
                default => redirect()->route('login'),
            };
        }
        return $next($request);
    }
}
