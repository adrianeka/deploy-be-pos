<?php

namespace App\Observers;

use App\Models\Pembayaran;
use App\Models\ArusKeuangan;

class PembayaranObserver
{
    /**
     * Handle the Pembayaran "created" event.
     */
    public function created(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "updated" event.
     */
    public function updated(Pembayaran $pembayaran): void
    {
        // Cek relasi pembayaran ke penjualan
        if ($pembayaran->pembayaranPenjualan) {
            $penjualan = $pembayaran->pembayaranPenjualan->penjualan;
            if ($penjualan) {
                // Ambil semua pembayaran penjualan urut sesuai waktu
                $pembayaranPenjualans = $penjualan->pembayaranPenjualan()->with('pembayaran')->orderBy('created_at')->get();
                $totalHarga = $penjualan->total_harga;
                $totalBayarSebelumnya = 0;

                foreach ($pembayaranPenjualans as $pp) {
                    $pembayaranSekarang = $pp->pembayaran->total_bayar;
                    $sisaKurang = $totalHarga - $totalBayarSebelumnya;
                    $nominalMasuk = min($pembayaranSekarang, max($sisaKurang, 0));

                    // Update ArusKeuangan untuk pembayaran ini
                    ArusKeuangan::where('id_sumber', $pp->pembayaran->id_pembayaran)
                        ->update(['nominal' => $nominalMasuk]);

                    $totalBayarSebelumnya += $pembayaranSekarang;
                }
            }
        }
        // Cek relasi pembayaran ke pembelian
        if ($pembayaran->pembayaranPembelian) {
            $pembelian = $pembayaran->pembayaranPembelian->pembelian;
            if ($pembelian) {
                // Ambil semua pembayaran pembelian urut sesuai waktu
                $pembayaranPembelians = $pembelian->pembayaranPembelian()->with('pembayaran')->orderBy('created_at')->get();
                $totalHarga = $pembelian->total_harga;
                $totalBayarSebelumnya = 0;

                foreach ($pembayaranPembelians as $pp) {
                    $pembayaranSekarang = $pp->pembayaran->total_bayar;
                    $sisaKurang = $totalHarga - $totalBayarSebelumnya;
                    $nominalMasuk = min($pembayaranSekarang, max($sisaKurang, 0));

                    // Update ArusKeuangan untuk pembayaran ini
                    ArusKeuangan::where('id_sumber', $pp->pembayaran->id_pembayaran)
                        ->update(['nominal' => $nominalMasuk]);

                    $totalBayarSebelumnya += $pembayaranSekarang;
                }
            }
        }
    }

    /**
     * Handle the Pembayaran "deleted" event.
     */
    public function deleted(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "restored" event.
     */
    public function restored(Pembayaran $pembayaran): void
    {
        //
    }

    /**
     * Handle the Pembayaran "force deleted" event.
     */
    public function forceDeleted(Pembayaran $pembayaran): void
    {
        //
    }
}
