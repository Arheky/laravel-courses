<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Kullanıcının rolünü kontrol eder.
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = auth('web')->user();

        if (! $user || $user->role !== $role) {
            abort(403, 'Bu sayfaya erişim izniniz bulunmuyor.');
        }

        return $next($request);
    }
}
