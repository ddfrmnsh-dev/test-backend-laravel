<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Laravel\Sanctum\PersonalAccessToken;
use Carbon\Carbon;

class EnsureSanctumTokenIsValid
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized: No token provided'], Response::HTTP_UNAUTHORIZED);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return response()->json(['message' => 'Unauthorized: Invalid token'], Response::HTTP_UNAUTHORIZED);
        }

        $expiration = config('sanctum.expiration');

        if ($expiration) {
            $expiredAt = $accessToken->created_at->addMinutes($expiration);
            if (Carbon::now()->greaterThan($expiredAt)) {
                return response()->json(['message' => 'Token expired'], Response::HTTP_UNAUTHORIZED);
            }
        }

        return $next($request);
    }
}
