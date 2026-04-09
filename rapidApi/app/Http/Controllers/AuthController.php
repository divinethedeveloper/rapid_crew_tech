<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\SignUpRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function signUp(SignUpRequest $request)
    {
        // return $request->all();
        $user = User::create([
            ...$request->all(),
            'name' => $request->fullname,
        ]);

        $user->token = $user->createToken('TheRapidCrew2020')->plainTextToken;

        return new UserResource($user);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            $user->token = $user->createToken('TheRapidCrew2020')->plainTextToken;

            return new UserResource($user);
            $user = User::where('email', $request->email)->first();
        }

        return response()->json([], 401);
    }
}
