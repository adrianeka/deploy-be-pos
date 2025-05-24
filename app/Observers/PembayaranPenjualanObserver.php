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
        $penjualan = $pembayaranPenjualan->penjualan;
        $totalHarga = $penjualan->total_harga;

        // Hitung total pembayaran sebelum pembayaran ini
        $totalBayarSebelumnya = $penjualan->pembayaran()
            ->where('pembayaran.id_pembayaran', '!=', $pembayaranPenjualan->pembayaran->id_pembayaran)
            ->sum('total_bayar');

        // Nominal yang benar-benar masuk kas (tidak termasuk kembalian)
        $sisaKurang = $totalHarga - $totalBayarSebelumnya;
        $nominalMasuk = min($pembayaranPenjualan->pembayaran->total_bayar, $sisaKurang);

        // Jika sudah lunas, nominalMasuk bisa 0
        if ($nominalMasuk <= 0) {
            return;
        }
        ArusKeuangan::create([
            'id_pemilik' => Filament::auth()->user()?->pemilik?->id_pemilik ?? $pembayaranPenjualan->penjualan->kasir->id_pemilik,
            'id_sumber' => $pembayaranPenjualan->pembayaran->id_pembayaran,
            'keterangan' => 'Pembayaran Penjualan ' . $pembayaranPenjualan->id_penjualan,
            'jenis_transaksi' => 'debit',
            'nominal' => $nominalMasuk,
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
