<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProduk extends CreateRecord
{
    protected static string $resource = ProdukResource::class;
    protected function afterCreate(): void
    {
        $record = $this->record;
        $hargaJual = $this->data['harga_jual'] ?? null; // Ambil data dari inputan langsung

        if ($hargaJual === null) {
            throw new \Exception('Harga jual tidak ditemukan');
        }

        // Buat Level Harga Standar
        $levelHarga = $record->levelHargas()->create([
            'nama_level' => 'Standard',
            'harga_jual' => $hargaJual,
            'is_applied' => true,
        ]);

        // Terapkan harga level harga standar sebagai harga jual produk
        $record->update([
            'harga_jual' => $levelHarga->harga_jual,
        ]);
    }
}
