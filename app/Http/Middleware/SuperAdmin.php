<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        // Jika belum login, arahkan ke login
        if (!$user) {
            return redirect()->route('login.view');
        }

        // Jika bukan super admin
        if ((int) $user->status !== 1) {
            return redirect()->route('order-view');
                // ->withErrors(['access' => 'Anda tidak memiliki akses ke halaman ini.']);
        }

        return $next($request);
    }
}
