<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware untuk membatasi akses berdasarkan role user.
 *
 * Penggunaan di route:
 *   ->middleware('role:admin')
 *   ->middleware('role:kecamatan')
 *   ->middleware('role:desa')
 *   ->middleware('role:admin,kecamatan')   ← multi-role (OR)
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Pastikan user sudah login
        if (! $request->user()) {
            return redirect()->route('login');
        }

        $userRole = $request->user()->role;

        // Cek apakah role user ada dalam daftar role yang diizinkan
        if (! in_array($userRole, $roles, strict: true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        return $next($request);
    }
}
