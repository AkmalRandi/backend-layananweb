<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function profile(Request $request)
    {
        // Sementara ambil user pertama, nanti pakai token
        $user = User::find(1);
        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }
        return response()->json([
            'status'  => true,
            'message' => 'Data user',
            'data'    => $user
        ]);
    }
}