<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Setting::getValue('maintenance_mode', false)) {
            $user = $request->user();

            if (!$user || $user->role !== 'admin') {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Sistem sedang dalam pemeliharaan.'], 503);
                }

                return response()->view('maintenance', [], 503);
            }
        }

        return $next($request);
    }
}
