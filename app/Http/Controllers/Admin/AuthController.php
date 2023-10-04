<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Carbon\Carbon;
use Illuminate\Support\Str;
use App\Http\Resources\Admin\AuthResource;
use App\Http\Requests\Admin\AuthRequest;

class AuthController extends Controller
{
    public function login(AuthRequest $request)
    {
        $validated = $request->validated();

        $admin = Admin::where('email', $validated['email'])->first();

        if (!$admin || !Hash::check($validated['password'], $admin->password)) {
            return Response([
                'error' => true,
                'message' => 'Username or Password is incorrect.'
            ], 401);
        } else {
            // if (!$admin->status) {
            //     return Response([
            //         'error' => true,
            //         'message' => 'You are not authorized.'
            //     ], 401);
            // }

            $admin->last_login = new \DateTime();
            $admin->auth_token = uniqid(base64_encode(Str::random(21)));
            $admin->token_expired_at  = (string) Carbon::createFromFormat('Y-m-d H:i:s', $validated['expired_date']);
            $admin->save();

            return new AuthResource($admin);
        }
    }

    public function logout(Request $request)
    {
        $validated = $request->validate([
            'admin_id' => 'required'
        ]);

        Admin::where("_id", $validated['admin_id'])->update(['auth_token' => null, 'token_expired_at' => null]);

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'User logout Successfully!',
        ]);
    }
}
