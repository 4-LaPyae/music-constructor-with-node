<?php

namespace App\Http\Middleware\Mobile;

use Closure;
use Illuminate\Http\Request;

class EnsureServerKeyIsValid
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
        if ($request->bearerToken()) {
            if ($request->bearerToken() === env('SOCKET_SERVER_KEY')) {
                return $next($request);
            } else {
                return Response()->json([
                    'error' => true,
                    'authorized' => false,
                    'message' => "Unauthorized,Invalid Server Key"
                ]);
            }
        } else {
            return Response()->json([
                'error' => true,
                'authorized' => false,
                'message' => "Unauthorized,Need Server Key"
            ]);
        }
    }
}