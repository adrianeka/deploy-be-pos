<?php

namespace App\Observers;

use App\Models\BayarZakat;
use App\Models\ArusKeuangan;
use Filament\Facades\Filament;

class BayarZakatObserver
{
    /**
     * Handle the BayarZakat "created" event.
     */
    public function created(BayarZakat $bayarZakat): void
    {
        $pembayaran = $bayarZakat->pembayaran;

        ArusKeuangan::create([
            'id_pemilik' => Filament::auth()->user()?->pemilik?->id_pemilik,
            'id_sumber' => $pembayaran->id_pembayaran,
            'keterangan' => 'Pembayaran Zakat ' . $bayarZakat->id_zakat,
            'jenis_transaksi' => 'kredit',
            'nominal' => $pembayaran->total_bayar,
        ]);
    }

    /**
     * Handle the BayarZakat "updated" event.
     */
    public function updated(BayarZakat $bayarZakat): void
    {
        //
    }

    /**
     * Handle the BayarZakat "deleted" event.
     */
    public function deleted(BayarZakat $bayarZakat): void
    {
        //
    }

    /**
     * Handle the BayarZakat "restored" event.
     */
    public function restored(BayarZakat $bayarZakat): void
    {
        //
    }

    /**
     * Handle the BayarZakat "force deleted" event.
     */
    public function forceDeleted(BayarZakat $bayarZakat): void
    {
        //
    }
}
