<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     // if (!Auth::guard('web')->check() || Auth::guard('web')->user()->role !== 'pemilik') {
    //     //     abort(403, 'Akses hanya untuk pemilik.');
    //     // }

    //     // return $next($request);
    // }
}
