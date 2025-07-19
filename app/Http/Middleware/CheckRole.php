<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response
    {
        $user = $request->user();

        if (!$user || $user->role !== $role) {
            // Kalau bukan role yang sesuai, return 403 forbidden atau redirect sesuai kebutuhan
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
