<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\V1\AuthMobileResource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\RateLimiter;

class AuthController extends Controller
{
    public function login(Request $request)
    {

        $validated = $request->validate([
            'phone' => 'required|string'
        ]);

        if (!Str::of($validated['phone'])->startsWith('0')) {
            $validated['phone'] = '0' . $validated['phone'];
        }

        $existsUser = User::where('phone',  $validated['phone'])->first();

        if (!isset($existsUser)) {
            //if user doesn't exits
            return response()->json([
                'error' => true,
                'message' => 'You need to contact admin',
            ]);
        } else {

            //for tester
            if ($existsUser->phone == '09999999999') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 999999
                ]);
            }

            if ($existsUser->phone == '09888888888') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 888888
                ]);
            }

            if ($existsUser->status == 1) {
                //generate otp
                $otp = rand(100000, 999999);

                //save new otp
                $existsUser->otp = $otp;
                $existsUser->save();

                // MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);

                //response
                return response()->json([
                    'error' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            } else {
                if (isset($existsUser->auth_token)) {
                    //if user exits but ban
                    return response()->json([
                        'error' => true,
                        'message' => 'Your number' . ' ' . $existsUser->phone . ' ' . 'is restricted. Please contact Admin',
                    ]);
                } else {
                    //if user exits but need to fill full detail
                    return response()->json([
                        'error' => true,
                        'message' => 'You need to contact admin',
                    ]);
                }
            }
        }
    }
    public function checkOTP(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|integer',
        ]);

        if (!Str::of($validated['phone'])->startsWith('0')) {
            $validated['phone'] = '0' . $validated['phone'];
        }

        $existsUser = User::where([
            ['phone', $validated['phone']],
            ['otp', (int)$validated['otp']]   // change to integer
        ])->first();

        if (!isset($existsUser)) {
            return response()->json([
                'error' => true,
                'authorize' => true,
                'message' => 'Invalid OTP code',
            ]);
        } else {
            //for tester
            if ($existsUser->phone == '09999999999') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
                $existsUser->token_expired_at = Carbon::now()->addWeeks(4)->toW3cString();
                $existsUser->status = 1;
                $existsUser->profile = $existsUser->profile ?? null;
                $existsUser->save();
                //response
                return new AuthMobileResource($existsUser);
            }

            //for tester
            if ($existsUser->phone == '09888888888') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
                $existsUser->token_expired_at = Carbon::now()->addWeeks(4)->toW3cString();
                $existsUser->status = 1;
                $existsUser->profile = $existsUser->profile ?? null;
                $existsUser->save();
                //response
                return new AuthMobileResource($existsUser);
            }

            $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
            $existsUser->token_expired_at = Carbon::now()->addWeeks(4)->toW3cString();
            $existsUser->status = 1;
            $existsUser->profile = $existsUser->profile ?? null;
            $existsUser->save();

            //! response
            return new AuthMobileResource($existsUser);
        }
    }

    public function resendOTP(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
        ]);

        if (!Str::of($validated['phone'])->startsWith('0')) {
            $validated['phone'] = '0' . $validated['phone'];
        }

        //for tester
        if ($validated['phone'] == '09999999999') {
            RateLimiter::clear(md5('day' . $validated['phone']));
            //response
            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'OTP sent successfully',
                'otp' => 999999
            ]);
        }

        if ($validated['phone'] == '09888888888') {
            RateLimiter::clear(md5('day' . $validated['phone']));
            //response
            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'OTP sent successfully',
                'otp' => 888888
            ]);
        }

        $otp = rand(100000, 999999);

        $existsUser = User::where('phone', $validated['phone'])->first();
        if ($existsUser) {
            $existsUser->otp = $otp;
            $existsUser->save();
            //  MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);
            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'OTP resent successfully',
                'otp' => $otp
            ]);
        }
    }
    public function logout(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string'
        ]);

        User::where("user_id", $validated['user_id'])->update(['auth_token' => null, 'token_expired_at' => null]);


        //!log activity
        // $this->activity($validated['user_id'], ConsumerActivityEnum::LOGOUT->toString());

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'User logout Successfully!',
        ]);
    }
}
