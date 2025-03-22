<?php

namespace App\Filament\Resources\KasirResource\Pages;

use App\Filament\Resources\KasirResource;
use App\Models\Kasir;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;

class CreateKasir extends CreateRecord
{
    protected static string $resource = KasirResource::class;
    protected static ?string $title = 'Tambah Kasir';

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Remove password_confirmation from data
        unset($data['password_confirmation']);
        return $data;
    }

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            // First create the user
            $user = User::create([
                'name' => $data['nama'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'kasir',
            ]);

            // Then create the kasir
            return Kasir::create([
                'id_user' => $user->id,
                'id_pemilik' => $data['id_pemilik'] ?? Filament::auth()->id(),
                'nama' => $data['nama'],
                'no_telp' => $data['no_telp'],
                'alamat' => $data['alamat'],
            ]);
        });
    }
}
