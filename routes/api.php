<?php

use App\Http\Controllers\PembelianController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProdukController;

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/cek-token', [AuthController::class, 'checkToken']);
    // Route::post('/pembelian', [PembelianController::class, 'store']);
    // Pelanggan
    Route::apiResource('pelanggan', PelangganController::class);
    Route::get('/pelanggan/penjualan/{id}', [PelangganController::class, 'getPenjualan']);
    // List Menu
    Route::apiResource('/menu', ProdukController::class);
    Route::get('/kategori', [ProdukController::class, 'getAllKategori']);
    // Transaksi Penjualan
    Route::apiResource('/penjualan', PenjualanController::class);
    Route::get('/cek-stok/{id}', [PenjualanController::class, 'cekStok']);
    Route::post('/bayar-penjualan/{id}', [PenjualanController::class, 'bayarPenjualan']);
    Route::post('/ambil-barang/{id}', [PenjualanController::class, 'barangSudahDiambil']);
    Route::get('/metode-pembayaran', [PenjualanController::class, 'getAllMetodePembayaran']);
    Route::get('/level-harga', [PenjualanController::class, 'getLevelHargas']);
    Route::post('/penjualan/retur-bulk/{id}', [PenjualanController::class, 'returProdukBulk']);
    Route::post('/penjualan/retur/{id}', [PenjualanController::class, 'returProduk']);
});
