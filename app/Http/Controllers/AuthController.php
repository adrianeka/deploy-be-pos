<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $token = $user->createToken('auth')->plainTextToken;

        if ($user->role === 'kasir') {
            $kasir = Kasir::where('id_user', $user->id)->first();
            if (!$kasir) {
                return response()->json(['message' => 'Cashier data not found'], 404);
            }
            return response()->json(['token' => $token, 'role' => 'kasir', 'kasir' => $kasir]);
        }
        return response()->json(['message' => 'Invalid role'], 403);

        // if ($user->role !== 'kasir') {
        //     return response()->json(['message' => 'Access denied'], 403);
        // }

        //$token = $user->createToken('auth')->plainTextToken;

        // return response()->json(['token' => $token]);
    }

    public function logout(Request $request): JsonResponse
    {
        // Pastikan user terautentikasi
        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // Hapus token hanya milik user yang sedang login
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil!']);
    }
}
