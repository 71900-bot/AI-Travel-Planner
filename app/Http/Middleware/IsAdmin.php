<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/signin');
        }

        $user = Auth::user();
        if (!$user || !($user->is_admin ?? false)) {
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}

