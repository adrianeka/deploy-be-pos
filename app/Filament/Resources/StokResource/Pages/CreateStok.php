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
        $data['id_pemilik'] = Filament::auth()->user()?->pemilik?->id_pemilik;
        return $data;
    }

    protected function beforeCreate(): void
    {
        $data = $this->form->getState();

        if ($data['jenis_stok'] === 'Out') {
            $currentStock = Stok::getStokTersediaByProduk($data['id_produk']);

            if ($currentStock < $data['jumlah_stok']) {
                Notification::make()
                    ->title('Stok Tidak Mencukupi')
                    ->body("Jumlah stok keluar ({$data['jumlah_stok']}) melebihi stok tersedia ({$currentStock})")
                    ->danger()
                    ->persistent()
                    ->send();

                $this->halt();
            }
        }
    }

    protected function handleRecordCreation(array $data): Stok
    {
        return Stok::create($data);
    }

    protected function getHaltFormSubmissionMessage(): ?string
    {
        return null;
    }

    protected function getRedirectUrl(): string
    {
        return StokResource::getUrl('view', [
            'record' => $this->form->getState()['id_produk'],
        ]);
    }
}
