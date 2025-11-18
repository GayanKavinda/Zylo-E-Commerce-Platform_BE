<?php
// app/http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        // Check if user has any of the allowed Spatie roles
        if (!$user || !$user->hasAnyRole($roles)) {
            $roleList = implode(', ', $roles);
            $message = "Access Denied. Allowed roles: {$roleList}.";
            return response()->json(['message' => $message], 403);
        }

        return $next($request);
    }
}