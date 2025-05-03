<?php

namespace App\Http\Controllers;

use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        try {
            $produkQuery = Produk::with(['kategori', 'satuan', 'pemilik', 'level_hargas']);
            // Apply filters if they exist
            $this->applyFilters($produkQuery, $request);

            $produkList = $produkQuery->get();

            $data = $produkList->map(function ($produk) {
                return $this->formatProdukData($produk);
            });

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data produk',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getAllKategori()
    {
        try {
            $kategoriQuery = Kategori::query();

            if ($idPemilik = request('id_pemilik')) {
                $kategoriQuery->where('id_pemilik', $idPemilik);
            }

            return response()->json([
                'success' => true,
                'data' => $kategoriQuery->get()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data kategori',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Apply filters to the produk query
     */
    protected function applyFilters($query, $request)
    {
        if ($idPemilik = $request->query('id_pemilik')) {
            $query->whereHas('pemilik', function ($q) use ($idPemilik) {
                $q->where('id_pemilik', $idPemilik);
            });
        }

        if ($idKategori = $request->query('id_kategori')) {
            $query->where('id_kategori', $idKategori);
        }
    }

    /**
     * Format produk data for response
     */
    protected function formatProdukData($produk)
    {
        return [
            'id_produk' => $produk->id_produk,
            'nama_produk' => $produk->nama_produk,
            'foto_produk' => $produk->foto_produk,
            'harga_beli' => $produk->harga_beli,
            'harga_standart' => $produk->harga_standart,
            'stok_minimum' => $produk->stok_minimum,
            'stok_tersedia' => Stok::getStokTersediaByProduk($produk->id_produk),
            'deskripsi' => $produk->deskripsi,
            'kategori' => $produk->kategori->nama_kategori ?? null,
            'satuan' => $produk->satuan->nama_satuan ?? null,
            'level_harga' => $produk->level_hargas
        ];
    }
}