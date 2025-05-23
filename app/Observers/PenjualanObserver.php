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
            Log::info("MASUK NOTIFY PENJUALAN");
            Notification::make()
                ->title('Transaksi Baru: ' . $this->penjualan->id_penjualan)
                ->body('Total Rp ' . number_format($this->penjualan->total_harga))
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
                
            Log::info("MASUK OBSERVER PENJUALAN NICH");
            
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
        //
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
