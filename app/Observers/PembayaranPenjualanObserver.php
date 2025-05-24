<?php

namespace App\Observers;

use App\Models\PembayaranPenjualan;
use App\Models\ArusKeuangan;
use Filament\Facades\Filament;

class PembayaranPenjualanObserver
{
    /**
     * Handle the PembayaranPenjualan "created" event.
     */
    public function created(PembayaranPenjualan $pembayaranPenjualan): void
    {
        ArusKeuangan::create([
            'id_pemilik' => 1,
            'id_sumber' => $pembayaranPenjualan->pembayaran->id_pembayaran,
            'keterangan' => 'Pembayaran Penjualan ' . $pembayaranPenjualan->id_penjualan,
            'jenis_transaksi' => 'debit',
            'nominal' => $pembayaranPenjualan->pembayaran->total_bayar,
            'created_at' => $pembayaranPenjualan->created_at,
        ]);
    }

    /**
     * Handle the PembayaranPenjualan "updated" event.
     */
    public function updated(PembayaranPenjualan $pembayaranPenjualan): void
    {
        //
    }

    /**
     * Handle the PembayaranPenjualan "deleted" event.
     */
    public function deleted(PembayaranPenjualan $pembayaranPenjualan): void
    {
        //
    }

    /**
     * Handle the PembayaranPenjualan "restored" event.
     */
    public function restored(PembayaranPenjualan $pembayaranPenjualan): void
    {
        //
    }

    /**
     * Handle the PembayaranPenjualan "force deleted" event.
     */
    public function forceDeleted(PembayaranPenjualan $pembayaranPenjualan): void
    {
        //
    }
}
