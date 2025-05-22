<?php

namespace App\Filament\Resources\ArusKeuanganResource\Pages;

use App\Filament\Resources\ArusKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Filament\Resources\PembelianResource;
use App\Models\ArusKeuangan;
use App\Models\Pembayaran;
use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CreateArusKeuangan extends CreateRecord
{
    protected static string $resource = ArusKeuanganResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {

            $pembayaran = Pembayaran::create([
                'total_bayar' => $data['nominal'] ?? 0,
                'jenis_pembayaran' => $data['metode_pembayaran'],
                'id_tipe_transfer' => $data['metode_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null,
                'keterangan' => '-',
            ]);

            $arusKeuangan = ArusKeuangan::create([
                'id_pemilik' => Filament::auth()->user()?->id,
                'id_sumber' => $pembayaran->id_pembayaran,
                'keterangan' => $data['keterangan'],
                'jenis_transaksi' => $data['jenis_transaksi'],
                'nominal' => $pembayaran->total_bayar,
            ]);

            return $arusKeuangan;
        });
    }
}
