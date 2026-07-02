<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the tenant for the request from the authenticated user and binds it
 * into the CurrentTenant singleton so the tenant global scope can apply. Guest
 * requests pass through without a tenant.
 */
class SetCurrentTenant
{
    public function __construct(protected CurrentTenant $currentTenant) {}

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->tenant) {
            $this->currentTenant->set($user->tenant);
        }

        return $next($request);
    }
}
