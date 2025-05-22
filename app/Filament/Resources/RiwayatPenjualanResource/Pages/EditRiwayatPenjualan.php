<?php

namespace App\Filament\Resources\RiwayatPenjualanResource\Pages;

use App\Filament\Resources\RiwayatPenjualanResource;
use App\Models\Pembayaran;
use App\Models\Penjualan;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditRiwayatPenjualan extends EditRecord
{
    protected static string $resource = RiwayatPenjualanResource::class;
    protected static ?string $title = 'Edit Transaksi Penjualan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $total = collect($data['penjualanDetail'] ?? [])
                ->sum(fn($item) => $item['sub_total_harga'] ?? 0);

            $diskon = $data['diskon'] ?? 0;
            $totalSetelahDiskon = $total - $diskon;

            $pembayaranItems = collect($data['pembayaranPenjualan'] ?? []);
            $totalPembayaran = 0;

            foreach ($pembayaranItems as $item) {
                if (isset($item['id_pembayaran']) && $item['id_pembayaran']) {
                    $pembayaran = Pembayaran::find($item['id_pembayaran']);
                    if ($pembayaran) {
                        $pembayaran->update([
                            'total_bayar' => $item['total_bayar'],
                            'jenis_pembayaran' => $item['jenis_pembayaran'],
                            'id_tipe_transfer' => $item['id_tipe_transfer'] ?? null,
                            'keterangan' => $item['keterangan'] ?? null,
                        ]);
                    }
                }
                $totalPembayaran += $item['total_bayar'] ?? 0;
            }

            $statusPenjualan = $totalPembayaran >= $totalSetelahDiskon ? 'lunas' : 'belum lunas';
            if ($record->status_penjualan === 'pesanan') {
                $statusPenjualan = $record->status_penjualan;
            }

            $record->update([
                'id_pelanggan' => $data['id_pelanggan'],
                'id_kasir' => $data['id_kasir'],
                'total_harga' => $totalSetelahDiskon,
                'diskon' => $diskon,
                'status_penjualan' => $statusPenjualan,
                'uang_diterima' => $totalPembayaran,
                'uang_kembalian' => $totalPembayaran > $totalSetelahDiskon ? $totalPembayaran - $totalSetelahDiskon : 0,
                'sisa_bayar' => $totalPembayaran < $totalSetelahDiskon ? $totalSetelahDiskon - $totalPembayaran : 0,
            ]);

            return $record;
        });
    }



    protected function mutateFormDataBeforeFill(array $data): array
    {
        $penjualan = Penjualan::with(['penjualanDetail', 'pembayaranPenjualan.pembayaran'])
            ->find($this->record->id_penjualan);

        if (!$penjualan) {
            return $data;
        }

        foreach ($penjualan->pembayaranPenjualan as $index => $pembayaranRelation) {
            $pembayaran = $pembayaranRelation->pembayaran;
            if ($pembayaran) {
                $data['pembayaranPenjualan'][$index]['total_bayar'] = $pembayaran->total_bayar;
                $data['pembayaranPenjualan'][$index]['jenis_pembayaran'] = $pembayaran->jenis_pembayaran;
                $data['pembayaranPenjualan'][$index]['id_tipe_transfer'] = $pembayaran->id_tipe_transfer;
                $data['pembayaranPenjualan'][$index]['keterangan'] = $pembayaran->keterangan;

                if ($pembayaran->id_tipe_transfer) {
                    $tipeTransfer = \App\Models\TipeTransfer::find($pembayaran->id_tipe_transfer);
                    if ($tipeTransfer) {
                        $data['pembayaranPenjualan'][$index]['tipe_pembayaran'] = $tipeTransfer->metode_transfer;
                    }
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $penjualanDetail = $data['penjualanDetail'] ?? [];
        $totalHarga = 0;

        foreach ($penjualanDetail as $item) {
            $totalHarga += $item['sub_total_harga'] ?? 0;
        }

        $diskon = $data['diskon'] ?? 0;
        $totalSetelahDiskon = $totalHarga - $diskon;

        $pembayaranItems = $data['pembayaranPenjualan'] ?? [];
        $totalPembayaran = 0;

        foreach ($pembayaranItems as $item) {
            $totalPembayaran += $item['total_bayar'] ?? 0;
        }

        $data['total_harga'] = $totalSetelahDiskon;
        $data['status_penjualan'] = $totalPembayaran >= $totalSetelahDiskon ? 'lunas' : 'belum lunas';
        $data['uang_diterima'] = $totalPembayaran;
        $data['uang_kembalian'] = $totalPembayaran > $totalSetelahDiskon ? $totalPembayaran - $totalSetelahDiskon : 0;
        $data['sisa_bayar'] = $totalPembayaran < $totalSetelahDiskon ? $totalSetelahDiskon - $totalPembayaran : 0;

        return $data;
    }
}
