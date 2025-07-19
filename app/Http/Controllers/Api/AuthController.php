<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        Log::info('Register request data:', $request->all());

        $request->validate([
            'name' => 'required|string',
            'email' => 'required',
            'password' => 'required|min:8|confirmed',
            'role' => 'required|in:kasir,manager'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => $request->role
        ]);

        Log::info('User created:', $user->toArray());
        return response()->json([
            'message' => 'User created successfully',
            'user' => $user
        ], 201);
    }

    public function login(Request $request)
    {
        //
        // Cek kredensial user
        if (!Auth::attempt($request->only('email', 'password'))) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah!'],
            ]);
        }

        // Regenerate session untuk keamanan
        $request->session()->regenerate();

        // Return user data tanpa token
        return response()->json([
            'message' => 'Login berhasil',
            'user' => Auth::user(),
        ]); $credentials = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    if (!Auth::attempt($credentials)) {
        return response()->json(['message' => 'Email atau password salah'], 401);
    }

    $request->session()->regenerate();

    return response()->json([
        'message' => 'Login berhasil',
        'user' => Auth::user(),
    ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Berhasil logout',
        ]);
    }

    public function user(Request $request)
    {
        return response()->json(
            $request->user()
        );
    }
}
