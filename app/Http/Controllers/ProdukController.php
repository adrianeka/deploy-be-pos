<?php

namespace App\Http\Controllers;

use App\Models\Produk;
use App\Models\Stok;
use Illuminate\Support\Facades\Request;

class ProdukController extends Controller
{
    public function index(Request $request)
    {
        $idPemilik = Request::query('id_pemilik');// ambil dari query string
        $idKategori = Request::query('id_kategori');

        $produkQuery = Produk::with(['kategori', 'satuan', 'pemilik', 'level_hargas']);

        if ($idPemilik) {
            $produkQuery->whereHas('pemilik', function ($query) use ($idPemilik) {
                $query->where('id_pemilik', $idPemilik);
            });
        }

        if ($idKategori) {
            $produkQuery->where('id_kategori', $idKategori);
        }

        $produkList = $produkQuery->get();

        $produkList->transform(function ($produk) {
            $produk->stok_tersedia = Stok::getStokTersediaByProduk($produk->id_produk);
            return $produk;
        });

        return $produkList;
    }
}