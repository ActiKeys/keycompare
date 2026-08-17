<?php

namespace App\Http\Middleware;

use App\Http\Controllers\InstallerController;
use Closure;
use Illuminate\Http\Request;

class NotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (InstallerController::isInstalled()) {
            return redirect('/');
        }
        return $next($request);
    }
}
