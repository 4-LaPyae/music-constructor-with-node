<?php

namespace App\Http\Controllers\Web\V1\Auth;

use App\Fileoperations\MobileUserOperation;
use App\Fileoperations\UserAdminOperation;
use App\Hashingoperations\HashingOperations;
use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\User\V1\AuthRequest;
use App\Http\Resources\Mobile\V1\AuthMobileResource;
use App\Jobs\Mobile\MakeOTPRequestMobile;
use App\Jobs\Mobile\MakeUserPlaylist;
use App\Jobs\Mobile\MakeUserProfileChanges;
use App\Models\Enduser;
use Carbon\Carbon;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

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

        $existsUser = Enduser::where('phone',  $validated['phone'])->first();

        if (!isset($existsUser)) {
            //if user doesn't exits
            return response()->json([
                'error' => true,
                'authorize' => true,
                'too_many' => false,
                'message' => 'You need to register',
            ]);
        } else {

            //for tester
            if ($existsUser->phone == '09999999999') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 999999
                ]);
            }

            if ($existsUser->phone == '09888888888') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 888888
                ]);
            }

            if ($existsUser->phone == '09111111111') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 111111
                ]);
            }

            if ($existsUser->status == 1) {
                //generate otp
                $otp = rand(100000, 999999);

                //save new otp
                $existsUser->otp = $otp;
                $existsUser->save();

                MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);

                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            } else {
                if (isset($existsUser->auth_token)) {
                    //if user exits but ban
                    return response()->json([
                        'error' => true,
                        'authorize' => true,
                        'too_many' => false,
                        'message' => 'Your number' . ' ' . $existsUser->phone . ' ' . 'is restricted. Please contact 09750066909',
                    ]);
                } else {
                    //if user exits but need to fill full detail
                    return response()->json([
                        'error' => true,
                        'authorize' => true,
                        'too_many' => false,
                        'message' => 'You need to register',
                    ]);
                }
            }
        }
    }

    public function generateUsername($name, $phone)
    {
        $name = str_replace(' ', '', (strtolower($name)));
        $length = rand(1, strlen($name));
        $char = $phone . $name;

        $existsUsername = Enduser::where('username', $name)->first();

        $username = $existsUsername ? $name . rand_str($length, $char) : $name;

        return $username;
    }

    public function register(Request $request)
    {

        $validated = $request->validate([
            'phone' => 'required|string',
            'name' => 'required|string',
        ]);

        if (!Str::of($validated['phone'])->startsWith('0')) {
            $validated['phone'] = '0' . $validated['phone'];
        }



        // $string =  json_encode([
        //     "phone" => $validated['phone'],
        //     "name" => $validated['name'],
        //     "expired_time" =>  Carbon::now()->addMonth(3)->toW3cString()
        // ]);

        $string = "ok nar sar";

        $privateKey = "SabanaWOWmeDoublePlusApplication";
        $secretKey = "SabanaWOWmeDoubl";
        $encryptMethod = 'AES-256-CBC';

        $encrypted =  HashingOperations::encrypt($privateKey, $secretKey, $encryptMethod, $string);

        // return $encrypted;
        // $decrypted =  HashingOperations::decrypt($privateKey, $secretKey, $encryptMethod, $encrypted);

        // return $decrypted;

        $username = $this->generateUsername($validated['name'], $validated['phone']);

        $existsUsername = Enduser::where('username', $username)->first();

        $validated['username'] = ($existsUsername) ? $this->generateUsername($validated['name'], $validated['phone']) : $username;

        $validated['user_master_key'] = $encrypted;

        $existsUser = Enduser::where([
            ['phone', $validated['phone']]
        ])->first();

        if ($existsUser) {
            //for tester
            if ($existsUser->phone == '09999999999') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 999999
                ]);
            }

            if ($existsUser->phone == '09888888888') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 888888
                ]);
            }

            if ($existsUser->phone == '09111111111') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => 111111
                ]);
            }

            //generate otp
            $otp = rand(100000, 999999);

            //save new otp
            $existsUser->name = $validated['name'];
            // $existsUser->username = $username;
            $existsUser->otp = $otp;
            $existsUser->save();

            MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);

            //response
            return response()->json([
                'error' => false,
                'authorize' => true,
                'too_many' => false,
                'message' => 'OTP sent successfully',
                'otp' => $otp
            ]);
        } else {
            if ($validated['phone'] == '09999999999') {

                $otp = 999999;

                Enduser::create($this->getData($validated, $otp));

                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            } else if ($validated['phone'] == '09888888888') {
                $otp = 888888;

                Enduser::create($this->getData($validated, $otp));

                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            } else if ($validated['phone'] == '09111111111') {
                $otp = 111111;

                Enduser::create($this->getData($validated, $otp));

                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            } else {
                //generate otp
                $otp = rand(100000, 999999);

                //create consumer
                $endUser = Enduser::create($this->getData($validated, $otp));
                MakeUserPlaylist::dispatchAfterResponse($endUser->user_id, null);
                MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);
                //response
                return response()->json([
                    'error' => false,
                    'authorize' => true,
                    'too_many' => false,
                    'message' => 'OTP sent successfully',
                    'otp' => $otp
                ]);
            }
        }
    }

    public function checkOTP(Request $request)
    {
        $validated = $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|integer',
            'fcm_token' => 'nullable',
            'platform' => 'nullable',
            'device_id' => 'nullable',
            'version' => 'nullable',
        ]);

        if (!Str::of($validated['phone'])->startsWith('0')) {
            $validated['phone'] = '0' . $validated['phone'];
        }

        $existsUser = Enduser::where([
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
                $existsUser->token_expired_at = Carbon::now()->addWeeks(1)->toW3cString();
                $existsUser->status = 1;
                $existsUser->profile = $existsUser->profile ?? null;
                $existsUser->fcm_token = $validated['fcm_token'] ?? $existsUser->fcm_token;
                $existsUser->platform = $validated['platform'] ?? $existsUser->platform;
                $existsUser->device_id = $validated['device_id'] ?? $existsUser->device_id;
                $existsUser->version = $validated['version'] ?? $existsUser->version;
                $existsUser->save();
                //response
                return new AuthMobileResource($existsUser);
            }

            //for tester
            if ($existsUser->phone == '09888888888') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
                $existsUser->token_expired_at = Carbon::now()->addWeeks(1)->toW3cString();
                $existsUser->status = 1;
                $existsUser->profile = $existsUser->profile ?? null;
                $existsUser->fcm_token = $validated['fcm_token'] ?? $existsUser->fcm_token;
                $existsUser->platform = $validated['platform'] ?? $existsUser->platform;
                $existsUser->device_id = $validated['device_id'] ?? $existsUser->device_id;
                $existsUser->version = $validated['version'] ?? $existsUser->version;
                $existsUser->save();
                //response
                return new AuthMobileResource($existsUser);
            }

            //for tester
            if ($existsUser->phone == '09111111111') {
                RateLimiter::clear(md5('day' . $validated['phone']));
                // $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
                $existsUser->auth_token = $existsUser->auth_token;
                $existsUser->token_expired_at = Carbon::now()->addWeeks(1)->toW3cString();
                $existsUser->status = 1;
                $existsUser->profile = $existsUser->profile ?? null;
                $existsUser->fcm_token = $validated['fcm_token'] ?? $existsUser->fcm_token;
                $existsUser->platform = $validated['platform'] ?? $existsUser->platform;
                $existsUser->device_id = $validated['device_id'] ?? $existsUser->device_id;
                $existsUser->version = $validated['version'] ?? $existsUser->version;
                $existsUser->save();
                //response
                return new AuthMobileResource($existsUser);
            }

            $existsUser->auth_token = uniqid(base64_encode(rand_str(200)));
            $existsUser->token_expired_at = Carbon::now()->addWeeks(1)->toW3cString();
            $existsUser->status = 1;
            $existsUser->profile = $existsUser->profile ?? null;
            $existsUser->fcm_token = $validated['fcm_token'] ?? $existsUser->fcm_token;
            $existsUser->platform = $validated['platform'] ?? $existsUser->platform;
            $existsUser->device_id = $validated['device_id'] ?? $existsUser->device_id;
            $existsUser->version = $validated['version'] ?? $existsUser->version;
            $existsUser->save();


            //! response
            return new AuthMobileResource($existsUser);
        }
    }

    public function userInfo(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string'
        ]);
        $user = Enduser::where('user_id', $validated['user_id'])->first();

        return  new AuthMobileResource($user);
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

        if ($validated['phone'] == '09111111111') {
            RateLimiter::clear(md5('day' . $validated['phone']));
            //response
            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'OTP sent successfully',
                'otp' => 111111
            ]);
        }

        $otp = rand(100000, 999999);

        $existsUser = Enduser::where('phone', $validated['phone'])->first();
        if ($existsUser) {
            $existsUser->otp = $otp;
            $existsUser->save();
            MakeOTPRequestMobile::dispatchAfterResponse($validated['phone'], $otp);
            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'OTP resent successfully',
                'otp' => $otp
            ]);
        }
    }

    public function checkTokenValid(Request $request)
    {

        $validated = $request->validate([
            'user_id' => 'required|string',
            "token" => 'required|string',
        ]);

        $exists = Enduser::where([
            ['user_id', (string) $validated['user_id']],
            ['auth_token', $validated['token']]
        ])->first();

        if (!$exists) {
            return Response()->json([
                'error' => true,
                'authorize' => true,
                'message' => "Unauthorized,Token Invalid"
            ]);
        } else {

            return Response()->json([
                'error' => false,
                'authorize' => true,
                'message' => "Token Valid"
            ]);
        }
    }

    public function userUpdateProfile(Request $request)
    {
        // return "update profile";
        // $validated = $request->validate([
        //     'name' => 'required|string',
        //     "profile" => 'nullable',
        // ]);

        $user = Enduser::where('user_id', $request->user_id)->first();

        $user->name = $request->name;

        //profile
        if (isset($request->profile)) {
            $operation = new MobileUserOperation($user->phone, $request->profile, null);
            $user->profile = $operation->StoreConsumersBase64Image();
        } else {
            $user->profile = $user->profile;
        }

        $user->save();
        //response
        return new AuthMobileResource($user);
    }
    
    public function deleteUser(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string',
            'otp' => 'required|integer'
        ]);

        $user = Enduser::where([
            ['user_id', $validated['user_id']],
            ['otp', $validated['otp']],
        ])->first();

        $user->name = 'Deleted User';
        $user->phone = '099999999999';
        $user->status = 0;

        if ($user->save()) {
            //!log activity
            // $this->activity($validated['user_id'], ConsumerActivityEnum::DELETE->toString());

            return response()->json([
                'error' => false,
                'authorize' => true,
                'message' => 'User Deleted Successfully!',
            ]);
        }
    }

    public function logout(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|string'
        ]);

        Enduser::where("user_id", $validated['user_id'])->update(['auth_token' => null, 'token_expired_at' => null, 'fcm_token' => null]);


        //!log activity
        //  $this->activity($validated['user_id'], ConsumerActivityEnum::LOGOUT->toString());

        return response()->json([
            'error' => false,
            'authorize' => true,
            'message' => 'User logout Successfully!',
        ]);
    }

    private function getData($validated, $otp)
    {
        return [
            'name' => $validated['name'],
            'username' => $validated['username'],
            'phone' =>  $validated['phone'],
            'otp' => $otp,
            'user_id' =>  uniqid(base64_encode(rand_str(1))) . bcrypt($validated['phone']),
            'status' => 0,
            'fcm_token' => null,
            'platform' => null,
            'device_id' => null,
            'version' => null,
            "user_master_key" => $validated['user_master_key']
        ];
    }
}
