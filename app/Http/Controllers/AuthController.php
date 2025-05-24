<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\LoginRequest;
use App\Models\Kasir;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function checkToken(Request $request): JsonResponse
    {
        try {
            // Jika middleware auth:sanctum sudah melewatkan request ke sini,
            // artinya token valid dan kita hanya perlu mengembalikan data user

            $user = $request->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token tidak valid',
                    'valid' => false
                ], 401);
            }

            // Jika user adalah kasir, tambahkan data kasir
            $response = [
                'success' => true,
                'message' => 'Token valid',
                'valid' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role
                ]
            ];

            if ($user->role === 'kasir') {
                $kasir = Kasir::where('id_user', $user->id)->first();
                if ($kasir) {
                    $response['kasir'] = $kasir;
                }
            }

            return response()->json($response);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memverifikasi token',
                'error' => $e->getMessage(),
                'valid' => false
            ], 500);
        }
    }
    public function login(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }

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
            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
                'token' => $token,
                'role' => 'kasir',
                'kasir' => $kasir
            ]);
        }
        return response()->json(['message' => 'Invalid role'], 403);
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
