<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsMerchant
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if (! in_array($role, ['merchant', 'admin'])) {
            return response()->json(['message' => 'Accès réservé aux commerçants.'], 403);
        }

        return $next($request);
    }
}