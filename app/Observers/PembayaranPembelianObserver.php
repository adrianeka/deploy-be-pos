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
        $pembelian = $pembayaranPembelian->pembelian;
        $totalHarga = $pembelian->total_harga;

        // Hitung total pembayaran sebelum pembayaran ini
        $totalBayarSebelumnya = $pembelian->pembayaran()
            ->where('pembayaran.id_pembayaran', '!=', $pembayaranPembelian->pembayaran->id_pembayaran)
            ->sum('total_bayar');

        // Nominal yang benar-benar masuk kas (tidak termasuk kembalian)
        $sisaKurang = $totalHarga - $totalBayarSebelumnya;
        $nominalMasuk = min($pembayaranPembelian->pembayaran->total_bayar, $sisaKurang);

        // Jika sudah lunas, nominalMasuk bisa 0
        if ($nominalMasuk <= 0) {
            return;
        }
        ArusKeuangan::create([
            'id_pemilik' => Filament::auth()->user()?->pemilik?->id_pemilik ?? $pembayaranPembelian->pembelian->pemasok->id_pemilik,
            'id_sumber' => $pembayaranPembelian->pembayaran->id_pembayaran,
            'keterangan' => 'Pembayaran Pembelian ' . $pembayaranPembelian->id_pembelian,
            'jenis_transaksi' => 'kredit',
            'nominal' => $nominalMasuk,
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
