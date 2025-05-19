<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pelanggan;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Exception;

class PelangganController extends Controller
{
    public function index()
    {
        try {
            $pelanggan = Pelanggan::all();
            return response()->json(['success' => true, 'data' => $pelanggan], 200);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            // Validasi input
            $validated = $request->validate([
                'id_pemilik' => 'required|exists:pemilik,id_pemilik',
                'nama_pelanggan' => 'required|string|max:255',
                'no_telp' => 'required|string|max:15',
                'alamat' => 'required|string',
            ]);

            $pelanggan = Pelanggan::create($validated);

            return response()->json(['success' => true, 'data' => $pelanggan], 201);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menambahkan pelanggan'], 500);
        }
    }

    public function show($id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);
            return response()->json(['success' => true, 'data' => $pelanggan], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan'], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);

            // Validasi input
            $validated = $request->validate([
                'id_pemilik' => 'sometimes|required|exists:pemilik,id_pemilik',
                'nama_pelanggan' => 'sometimes|required|string|max:255',
                'no_telp' => 'sometimes|required|string|max:15',
                'alamat' => 'sometimes|required|string',
            ]);

            $pelanggan->update($validated);

            return response()->json(['success' => true, 'data' => $pelanggan], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
        } catch (ValidationException $e) {
            return response()->json(['success' => false, 'message' => $e->errors()], 422);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal memperbarui pelanggan'], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $pelanggan = Pelanggan::findOrFail($id);
            $pelanggan->delete();

            return response()->json(['success' => true, 'message' => 'Pelanggan berhasil dihapus'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal menghapus pelanggan'], 500);
        }
    }
    public function getPenjualan($id){
        try{
            $pelanggan = Pelanggan::with([
                'penjualan.kasir',
            ])->findOrFail($id);

            $data = $pelanggan->penjualan->map(function ($penjualan) {
                return [
                    'waktu_penjualan' => $penjualan->created_at->format('Y-m-d H:i:s'),
                    'nomor_transaksi' => $penjualan->id_penjualan,
                    'nama_kasir' => $penjualan->kasir?->nama ?? '-',
                    'status_penjualan' => $penjualan->status_penjualan,
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Penjualan berhasil didapatkan',
                'data' => $data
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Pelanggan tidak ditemukan'], 404);
        } catch (Exception $e) {
            return response()->json(['success' => false, 'message' => 'Gagal mendapakatkan penjualan dari pelanggan ini'], 500);
        }
    }
}
