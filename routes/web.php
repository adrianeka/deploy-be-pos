<?php

use App\Models\Penjualan;
use App\Notifications\TransaksiPenjualanNotification;
use App\Services\DummyReorderPointService;
use App\ReorderPointService;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;

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