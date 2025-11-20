<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class MonitoringAccess
{
    public function handle(Request $request, Closure $next)
    {
        // Kalau sudah login biasa (admin/user) → boleh langsung masuk
        if (auth()->check() && auth()->user()->status == 1) {
            return $next($request);
        }

        // Kalau belum login, cek session monitoring
        if (session('monitoring_access_granted')) {
            return $next($request);
        }

        // Kalau tidak ada izin → kembali ke login dengan pesan
        return redirect()->route('login.view')
            ->with('monitoring_error', 'Masukkan password monitoring terlebih dahulu.');
    }
}
