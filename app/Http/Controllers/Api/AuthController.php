<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function index() {
        return view('auth.login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'bpid' => 'required',
            'username' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['Username or password is incorrect']
            ], 404);
        }
        $user->generateOTP();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response([
            'message' => 'success',
            'user' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function register(Request $request) {
        $request->validate([
            'bpid' => 'required',
            'username' => 'required',
            'password' => 'required'
        ]);

        $user = User::where('username', $request->username)->first();

        if ($user) {
            return response([
                'message' => ['Username already exists']
            ], 404);
        }
        $user = User::create([
            'bpid' => $request->bpid,
            'username' => $request->username,
            'password' => Hash::make($request->password)
        ]);

        return response([
            'message' => 'User created successfully',
            'user' => $user
        ]);
    }

    public function logout(Request $request) {
        $request->user()->currentAccessToken()->delete();
        return response([
            'message' => 'success fully logout','kamu berhasil keluar dari program API'
        ]);
    }
}
