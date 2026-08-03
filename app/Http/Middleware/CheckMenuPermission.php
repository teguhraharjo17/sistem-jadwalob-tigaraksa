<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMenuPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Permission check only applies to logged in users.
        // Unauthenticated users will be handled by standard 'auth' middleware on protected routes.
        if (Auth::check()) {
            $user = Auth::user();
            $path = $request->path();

            // Mapping of URL prefixes to dynamic permission keys
            $permissionMap = [
                'dashboard' => 'dashboard',
                'checklist' => 'checklist',
                'laporanharian' => 'laporanharian',
                'superadmin' => 'master_data',
                'admin' => 'master_data',
            ];

            foreach ($permissionMap as $prefix => $permission) {
                if ($path === $prefix || str_starts_with($path, $prefix . '/')) {
                    if (!$user->hasPermission($permission)) {
                        abort(403, 'Unauthorized action.');
                    }
                }
            }
        }

        return $next($request);
    }
}
