<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class RoleCheck
{
    public function handle($request, Closure $next, $role)
    {
        // Cek jika user sudah login
        if (!Auth::check()) {
            return redirect('login'); // Jika tidak login, redirect ke halaman login
        }

        // Cek role user
        $user = Auth::user();
        if ($user->role !== $role) {
            return response()->json(['error' => 'Unauthorized'], 403); // Jika bukan admin, return error
        }

        return $next($request); // Jika role cocok, lanjutkan ke controller
    }
}
