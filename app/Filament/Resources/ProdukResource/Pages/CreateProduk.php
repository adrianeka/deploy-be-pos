<?php

namespace App\Filament\Resources\ProdukResource\Pages;

use App\Filament\Resources\ProdukResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateProduk extends CreateRecord
{
    protected static string $resource = ProdukResource::class;
    protected static ?string $title = 'Tambah Produk';

    protected function afterCreate(): void
    {
        $hargaJual = $this->data['harga_jual'];

        // Pastikan harga jual selalu integer
        $hargaJual = intval($hargaJual);

        // Buat Level Harga Standar
        $levelHarga =  $this->record->level_hargas()->create([
            'nama_level' => 'Standart',
            'harga_jual' => $hargaJual,
            'id_pemilik' => Filament::auth()->user()?->pemilik?->id_pemilik,
        ]);

        // Terapkan harga level harga standar sebagai harga jual produk
        $this->record->update([
            'harga_jual' => $levelHarga->harga_jual,
        ]);

        $this->record->stok()->create([
            'id_produk' => $this->record->id_produk,
            'jumlah_stok' => 0,
            'jenis_stok' => null,
            'jenis_transaksi' => null,
            'keterangan' => 'Stok Awal',
        ]);
    }
}
