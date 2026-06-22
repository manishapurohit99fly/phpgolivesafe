<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;

class CheckUserActive
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth('admin')->user();

        if (!$user) {
            return $next($request);
        }

        // Re-query from DB so we always see the current status/deleted_at,
        // not the stale session snapshot.
        $fresh = User::withTrashed()->find($user->id);

        if (!$fresh || $fresh->trashed() || (int) $fresh->status !== 1) {
            Auth::guard('admin')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')
                ->with('error', 'Your account has been deactivated or removed. Please contact the administrator.');
        }

        return $next($request);
    }
}
