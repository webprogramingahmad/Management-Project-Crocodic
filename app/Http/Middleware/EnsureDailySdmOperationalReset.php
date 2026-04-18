<?php

namespace App\Http\Middleware;

use App\Support\StatusSdmManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class EnsureDailySdmOperationalReset
{
    /**
     * Satu kali per hari (request pertama setelah berganti tanggal): reset status operasional semua SDM.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $key = 'sdm_operational_reset_'.now()->toDateString();
            if (Cache::add($key, true, now()->endOfDay())) {
                StatusSdmManager::dailyOperationalReset();
            }
        }

        return $next($request);
    }
}
