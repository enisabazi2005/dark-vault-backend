<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        // if (Auth::check()) {
        //     $user = Auth::user();

        //     // Check if the user is accessing the site for the first time or after logging in
        //     // Set status to online only if it's not already manually set
        //     if (!session('status_set') && !$user->status) {
        //         // Update user status to online if it hasn't been manually set
        //         $user->update(['online' => true, 'offline' => false, 'away' => false, 'do_not_disturb' => false]);
                
        //         // Set a session flag to ensure it only happens once
        //         session(['status_set' => true]);
        //     }
        // }

        return $next($request);
    }
}
