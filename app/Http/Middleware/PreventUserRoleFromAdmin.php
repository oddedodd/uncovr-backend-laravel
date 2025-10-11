<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PreventUserRoleFromAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has the 'user' role
        if (auth()->check() && auth()->user()->hasRole('user')) {
            // Redirect 'user' role away from admin panel
            return redirect('/')->with('error', 'You do not have permission to access the admin panel.');
        }

        return $next($request);
    }
}
