<?php

use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\Stok;
use App\Notifications\TransaksiPenjualanNotification;
use App\Services\DummyReorderPointService;
use App\ReorderPointService;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Route::get('/test-rop', function (ReorderPointService $service) {
    $result = $service->calculate();

    return response()->json($result);
});

Route::get('/test-waktu', function () {
    $now = Carbon::now()->format('Y-m-d H:i:s');
    return response()->json([
        'now' => $now,
    ]);
});

Route::get('/cek-stok-minimum/{id_produk}', function ($id_produk) {
    $produk = Produk::find($id_produk);

    if (!$produk) {
        return 'Produk tidak ditemukan';
    }

    $stokTersedia = Stok::getStokTersediaByProduk($produk->id_produk);

    Log::info('Cek stok minimum manual', [
        'produk_id' => $produk->id_produk,
        'nama_produk' => $produk->nama_produk,
        'stok_tersedia' => $stokTersedia,
        'stok_minimum' => $produk->stok_minimum,
    ]);

    if ($stokTersedia <= $produk->stok_minimum) {
        Log::info('Stok minimum tercapai (route web)', [
            'produk_id' => $produk->id_produk,
            'nama_produk' => $produk->nama_produk,
        ]);
        return 'Stok produk "' . $produk->nama_produk . '" sudah mencapai stok minimum!';
    } else {
        return 'Stok produk "' . $produk->nama_produk . '" masih aman.';
    }
});

// Broadcast::routes();
Broadcast::routes(['middleware' => ['auth']]);
Route::post('/broadcasting/auth', function (Request $request) {});

Route::get('/', function () {
    return redirect()->route('filament.admin.auth.login');
});

Route::get('/test-rop', function (ReorderPointService $service) {
    $result = $service->calculate();

    return response()->json($result);
});