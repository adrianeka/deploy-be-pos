<?php

namespace App\Http\Controllers;

use App\Models\Pembelian;
use App\Models\PembelianDetail;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Exception;

class PembelianController extends Controller
{
    /**
     * Store a new purchase transaction.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            // Validate request data
            $validated = $request->validate([
                'id_pemilik' => 'required|integer|exists:pemilik,id_pemilik',
                'id_pemasok' => 'required|integer|exists:pemasok,id_pemasok',
                'produk' => 'required|array|min:1',
                'produk.*.id_produk' => 'required|integer|exists:produk,id_produk',
                'produk.*.jumlah_produk' => 'required|integer|min:1',
            ]);

            $totalHarga = 0;
            $produkDetails = [];

            foreach ($validated['produk'] as $item) {
                $produk = Produk::where('id_produk', $item['id_produk'])
                    ->where('id_pemilik', $validated['id_pemilik'])
                    ->firstOrFail();

                $subtotal = $produk->harga_beli * $item['jumlah_produk'];
                $totalHarga += $subtotal;

                $produkDetails[] = [
                    'produk' => $produk,
                    'jumlah' => $item['jumlah_produk'],
                    'subtotal' => $subtotal
                ];
            }

            DB::beginTransaction();

            $pembelian = Pembelian::create([
                'id_pemasok' => $validated['id_pemasok'],
                'tanggal_pembelian' => now(),
                'total_harga' => $totalHarga,
                'status_pembelian' => 'diproses'
            ]);

            $detailItems = [];
            foreach ($produkDetails as $item) {
                $detailItem = new PembelianDetail([
                    'id_produk' => $item['produk']->id_produk,
                    'jumlah_produk' => $item['jumlah']
                ]);

                $pembelian->pembelianDetail()->save($detailItem);
                $detailItems[] = $detailItem;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi pembelian berhasil dibuat',
                'data' => [
                    'pembelian' => $pembelian,
                    'detail' => $detailItems,
                    'perhitungan_harga' => $produkDetails
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses transaksi',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
