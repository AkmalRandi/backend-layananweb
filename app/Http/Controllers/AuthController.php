<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    // 🔥 REGISTER KHUSUS SISWA (STUDENT)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users',
            'email'     => 'required|email|max:255|unique:users',
            'password'  => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // 🔥 ROLE OTOMATIS STUDENT (tidak bisa pilih teacher)
        $user = User::create([
            'full_name' => $request->full_name,
            'username'  => $request->username,
            'email'     => $request->email,
            'role'      => 'student', // 🔥 FIXED: selalu student
            'password'  => Hash::make($request->password)
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Registrasi berhasil! Akun siswa telah dibuat.',
            'data'    => [
                'id'        => $user->id,
                'full_name' => $user->full_name,
                'username'  => $user->username,
                'email'     => $user->email,
                'role'      => $user->role,
            ]
        ], 201);
    }

    // 🔥 LOGIN (untuk semua role)
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Username atau password salah'
            ], 401);
        }

        // Token berisi user_id dan role
        $payload = json_encode([
            'id'   => $user->id,
            'role' => $user->role
        ]);
        $token = base64_encode($payload);

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'        => $user->id,
                    'full_name' => $user->full_name,
                    'username'  => $user->username,
                    'email'     => $user->email,
                    'role'      => $user->role,
                ]
            ]
        ]);
    }

    public function logout(Request $request)
    {
        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil'
        ]);
    }
}