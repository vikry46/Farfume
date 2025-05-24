<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $roles): Response
    {
        // Pastikan user terautentikasi
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $allowedRoles = explode('|', $roles);
        
        // Periksa role user
        if (!in_array($request->user()->role, $allowedRoles)) {
            return response()->json([
                'message' => 'Forbidden: You don\'t have required role',
                'required_roles' => $allowedRoles,
                'your_role' => $request->user()->role
            ], 403);
        }

        return $next($request);
    }
}