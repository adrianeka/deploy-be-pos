<?php

namespace App\Observers;

use App\Models\PembayaranPembelian;
use App\Models\ArusKeuangan;
use Filament\Facades\Filament;

class PembayaranPembelianObserver
{
    /**
     * Handle the PembayaranPembelian "created" event.
     */
    public function created(PembayaranPembelian $pembayaranPembelian): void
    {
        ArusKeuangan::create([
            'id_pemilik' => 1,
            'id_sumber' => $pembayaranPembelian->pembayaran->id_pembayaran,
            'keterangan' => 'Pembayaran Pembelian ' . $pembayaranPembelian->id_pembelian,
            'jenis_transaksi' => 'kredit',
            'nominal' => $pembayaranPembelian->pembayaran->total_bayar,
            'created_at' => $pembayaranPembelian->created_at,
        ]);
    }

    /**
     * Handle the PembayaranPembelian "updated" event.
     */
    public function updated(PembayaranPembelian $pembayaranPembelian): void
    {
        //
    }

    /**
     * Handle the PembayaranPembelian "deleted" event.
     */
    public function deleted(PembayaranPembelian $pembayaranPembelian): void
    {
        //
    }

    /**
     * Handle the PembayaranPembelian "restored" event.
     */
    public function restored(PembayaranPembelian $pembayaranPembelian): void
    {
        //
    }

    /**
     * Handle the PembayaranPembelian "force deleted" event.
     */
    public function forceDeleted(PembayaranPembelian $pembayaranPembelian): void
    {
        //
    }
}
