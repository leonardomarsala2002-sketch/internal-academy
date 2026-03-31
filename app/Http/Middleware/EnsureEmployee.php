<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmployee
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            abort(403, 'Access denied.');
        }

        if (! $request->user()->isEmployee()) {
            return redirect()->route('admin.workshops.index');
        }

        return $next($request);
    }
}
