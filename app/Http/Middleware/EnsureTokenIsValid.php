<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Carbon\Carbon;

use Closure;
use Illuminate\Http\Request;

class EnsureTokenIsValid
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

        $token = Admin::where('_id',  $request->admin_id)->first(['auth_token', 'token_expired_at']);

        //return response()->json(["token" => $token]);
        // return $next($request);

        if (!isset($token)) {
            return Response()->json([
                'error' => true,
                'authorize' => true,
                'message' => "User doesn't exists"
            ]);
        } else {
            $checkExpired = Carbon::now() > Carbon::parse($token->token_expired_at);
            //return response()->json(["checkExpired" => Carbon::now()]);
            if ($checkExpired) {
                return Response()->json([
                    'error' => true,
                    'authorize' => false,
                    'message' => "Unauthorized,Token expired"
                ]);
            } else {
                if ($token->auth_token === $request->bearerToken()) {
                    return $next($request);
                } else {
                    return Response()->json([
                        'error' => true,
                        'authorize' => false,
                        'message' => "Unauthorized,Invalid token"
                    ]);
                }
            }
        }
    }
}
