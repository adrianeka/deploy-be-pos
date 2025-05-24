<?php

namespace App\Observers;

use App\Models\Penjualan;
use App\Models\User;
use App\Notifications\TransaksiPenjualanNotification;
use Exception;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PenjualanObserver
{
    private $penjualan;
    // NotifyPenjualan
    protected function notifyPenjualan($user){
        try {
            Notification::make()
                ->title('Transaksi Penjualan Baru: ' . $this->penjualan->id_penjualan)
                ->body(
                    "ID Penjualan: {$this->penjualan->id_penjualan}\n" .
                    "Total Harga Rp " . number_format($this->penjualan->total_harga, 0, ',', '.')
                )
                ->success()
                ->sendToDatabase($user);
                // ->broadcast($user);
        } catch (Exception $e) {
            Log::error('Error saat mengirim notifikasi: ' . $e->getMessage(), [
                'penjualan_id' => $this->penjualan->id_penjualan ?? null,
                'user_id' => $user->id ?? null
            ]);
        }
    }
    
    /**
     * Handle the Penjualan "created" event.
     */
    public function created(Penjualan $penjualan): void
    {
        try {
            $this->penjualan = Penjualan::with('kasir', 'penjualanDetail', 'penjualanDetail.produk')
                ->find($penjualan->id_penjualan);
            
            if (!$this->penjualan) {
                Log::warning("Penjualan tidak ditemukan", ['id' => $penjualan->id_penjualan]);
                return;
            }
            
            if (!$this->penjualan->kasir) {
                Log::warning("Kasir tidak ditemukan", ['penjualan_id' => $penjualan->id_penjualan]);
                return;
            }
            
            $pemilik = $this->penjualan->kasir->id_pemilik;
            
            if (!$pemilik) {
                Log::warning("ID pemilik tidak ditemukan", ['kasir_id' => $this->penjualan->kasir->id_kasir]);
                return;
            }
            
            $user = User::find($pemilik);
            
            if (!$user) {
                Log::warning("User tidak ditemukan", ['pemilik_id' => $pemilik]);
                return;
            }
            
            $this->notifyPenjualan($user);
            
        } catch (Exception $e) {
            Log::error('Error di PenjualanObserver: ' . $e->getMessage(), [
                'penjualan_id' => $penjualan->id_penjualan ?? null,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the Penjualan "updated" event.
     */
    public function updated(Penjualan $penjualan): void
    {
        // Cek apakah total_harga berubah
        if ($penjualan->wasChanged('total_harga')) {
            // Ambil semua pembayaran penjualan urut sesuai waktu
            $pembayaranPenjualans = $penjualan->pembayaranPenjualan()->with('pembayaran')->orderBy('created_at')->get();
            $totalHarga = $penjualan->total_harga;
            $totalBayarSebelumnya = 0;

            foreach ($pembayaranPenjualans as $pp) {
                $pembayaranSekarang = $pp->pembayaran->total_bayar;
                $sisaKurang = $totalHarga - $totalBayarSebelumnya;
                $nominalMasuk = min($pembayaranSekarang, max($sisaKurang, 0));

                // Update ArusKeuangan untuk pembayaran ini
                \App\Models\ArusKeuangan::where('id_sumber', $pp->pembayaran->id_pembayaran)
                    ->update(['nominal' => $nominalMasuk]);

                $totalBayarSebelumnya += $pembayaranSekarang;
            }
        }
    }

    /**
     * Handle the Penjualan "deleted" event.
     */
    public function deleted(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "restored" event.
     */
    public function restored(Penjualan $penjualan): void
    {
        //
    }

    /**
     * Handle the Penjualan "force deleted" event.
     */
    public function forceDeleted(Penjualan $penjualan): void
    {
        //
    }
}
