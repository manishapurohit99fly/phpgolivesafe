<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, int|string ...$roles): Response
    {
        $user = auth('admin')->user();
        $allowed = array_map('intval', $roles);

        if ($user && in_array((int) $user->role, $allowed, true)) {
            return $next($request);
        }

        // Soft redirect instead of hard 403 so the UX stays friendly
        if ($user) {            
            return (int) $user->role === 2
                ? redirect()->route('user.project.index')
                : redirect()->route('admin.dashboard');
        }

        return redirect()->route('admin.login');
    }
}
