<?php
// app/http/Middleware/RoleMiddleware.php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Split roles by comma to support multiple roles and trim whitespace
        $allowedRoles = array_map('trim', explode(',', $roles));
        
        // Check if user has any of the allowed roles (using simple role field)
        if (!in_array($user->role, $allowedRoles)) {
            return response()->json([
                'message' => 'Access Denied. Required role: ' . $roles,
                'your_role' => $user->role
            ], 403);
        }

        return $next($request);
    }
}