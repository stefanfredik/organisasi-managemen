<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        if (!Setting::getValue($feature, true)) {
            abort(404);
        }

        return $next($request);
    }
}
