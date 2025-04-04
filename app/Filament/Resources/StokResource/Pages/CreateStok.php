<?php

namespace App\Filament\Resources\StokResource\Pages;

use App\Filament\Resources\StokResource;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Stok;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;

class CreateStok extends CreateRecord
{
    protected static string $resource = StokResource::class;
    protected static ?string $title = 'Edit Stok Produk';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['id_pemilik'] = Filament::auth()->id();

        return $data;
    }

    protected function beforeCreate(): void
    {
        // Mengambil data form
        $data = $this->form->getState();

        // Cek jika jenis stok adalah keluar (Out)
        if ($data['jenis_stok'] === 'Out') {
            // Hitung stok tersedia saat ini
            $currentStock = Stok::getStokTersediaByProduk($data['id_produk']);

            // Jika stok tidak mencukupi
            if ($currentStock < $data['jumlah_stok']) {
                // Buat notifikasi
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->body("Jumlah stok keluar ({$data['jumlah_stok']}) melebihi stok tersedia ({$currentStock})")
                    ->danger()
                    ->persistent()
                    ->send();

                // Batalkan pembuatan record
                $this->halt();
            }
        }
    }

    // Override agar error dapat ditangkap
    protected function getHaltFormSubmissionMessage(): ?string
    {
        return null; // Tidak menampilkan pesan default
    }
}
