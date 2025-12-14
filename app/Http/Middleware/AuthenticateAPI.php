<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class AuthenticateAPI
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token not found!'
            ], 401);
        }

        $user = User::where('token', $token)->first();
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token is not valid!'
            ], 401);
        }

        auth()->login($user);
        
        return $next($request);
    }
}