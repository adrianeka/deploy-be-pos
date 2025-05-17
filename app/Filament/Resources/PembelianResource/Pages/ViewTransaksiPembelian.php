<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Filament\Resources\PembelianResource\RelationManagers\PembayaranRelationManager;
use App\Filament\Resources\PembelianResource\RelationManagers\ProdukRelationManager;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewTransaksiPembelian extends ViewRecord
{
    protected static string $resource = PembelianResource::class;

    protected function lanjutkanProses()
    {
        // Logika untuk lanjutkan proses
        $this->notify('success', 'Transaksi dilanjutkan.');
    }

    protected function lihatPembayaran()
    {
        // Logika lihat detail pembayaran
        $this->notify('info', 'Menampilkan detail pembayaran.');
    }

    protected function bayarSekarang()
    {
        // Logika untuk bayar sekarang
        $this->notify('success', 'Redirect ke halaman pembayaran.');
    }


    protected function getHeaderActions(): array
    {
        $status = $this->record->status_pembelian;

        $actions = [];

        if ($status === 'proses') {
            $actions[] = Actions\Action::make('lanjutkanProses')
                ->label('Konfirmasi Pembelian')
                ->action(fn() => $this->lanjutkanProses())
                ->color('pimary');
        }

        if ($status === 'belum lunas') {
            $actions[] = Actions\Action::make('bayarSekarang')
                ->label('Bayar Sekarang')
                ->action(fn() => $this->bayarSekarang())
                ->color('danger');
        }

        return $actions;
    }


    public function getRelationManagers(): array
    {
        return [
            ProdukRelationManager::class,
            PembayaranRelationManager::class,
        ];
    }
}
