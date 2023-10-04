<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class UserEnsureTokenIsValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($request->user_id) {
            $user = User::where('user_id', $request->user_id)->first(['auth_token']);
            // return response()->json(["token" => $user]);
            // return $next($request);
            if (!$user) {
                return response()->json([
                    "error" => true,
                    "message" => "User doesn't exists"
                ]);
            } else {
                if ($request->bearerToken() == $user->auth_token) {
                    return $next($request);
                } else {
                    return Response()->json([
                        'error' => true,
                        'message' => "Unauthorized,Invalid token"
                    ]);
                }
            }
        }
    }
}
