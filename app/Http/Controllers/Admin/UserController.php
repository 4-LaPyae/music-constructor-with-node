<?php

namespace App\Http\Controllers\Admin;

use App\Fileoperations\VendorUserOperation;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UserRequest;
use Carbon\Carbon;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = Request()->has('page') ? Request()->get('page') : 1;
        $limit = Request()->has('limit') ? Request()->get('limit') : 10;
        $userName = Request()->has('username') ? Request()->get('username') : '';

        $users = User::where(function ($query) use ($userName) {
            if (isset($userName)) {
                $query->where('name', 'LIKE', '%' . $userName . '%');
            }
            return $query;
        });

        return Response()->json([
            'data' => $users->orderBy('created_at', '-1')
                ->limit($limit)
                ->offset(($page - 1) * $limit)
                ->get(),
            'message' => 'User list.',
            'total' => $users->count(),
            'page' => (int)$page,
            'rowPerPages' => (int)$limit,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $validated = $request->validated();
        $user = User::where('phone', $request['phone'])->first();

        if ($user) {
            return Response()->json([
                'error' => true,
                "message" => "user already exist",
                //'user' => $user,

            ]);
        } else {

            $validated['otp'] = null;
            $validated['user_id'] =  uniqid(base64_encode(rand_str(1))) . bcrypt($validated['phone']);
            $validated['status'] = 1;
            $validated['token_expired_at'] = (string)Carbon::now();
            $validated['auth_token'] =  uniqid(base64_encode(Str::random(21)));

            if (isset($validated['profile'])) {
                $operation = new VendorUserOperation($validated['phone'], null, $validated['profile']);
                $validated['profile'] = $operation->StoreUserImage();
            } else {
                $validated['profile'] =  null;
            }

            $user = User::create($validated);
            return Response()->json([
                'error' => false,
                'user' => $user,
                "message" => "User created successfully"
            ]);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::where('_id', $id)->first();

        return Response()->json([
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(UserRequest $request, $id)
    {
        $validated = $request->validated();

        $user = User::where('_id', $id)->first();

        if (isset($validated['profile'])) {
            $operation = new VendorUserOperation($validated['phone'], null, $validated['profile']);
            $validated['profile'] = $operation->StoreUserImage();
        } else {
            $validated['profile'] =  $user->profile;
        }

        if ($user->update($validated)) {
            return Response()->json([
                "error" => false,
                "message" => "User updated successfully",
                'user' => $user,
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {

        $user = User::where('_id', $id)->first();
        $user->status = 0;

        if ($user->save()) {
            return Response()->json([
                "error" => false,
                "message" => "User deleted successfully",
            ]);
        }
    }
}
