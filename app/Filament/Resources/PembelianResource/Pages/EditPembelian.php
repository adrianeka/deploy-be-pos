<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Models\Pembayaran;
use App\Models\Pembelian;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class EditPembelian extends EditRecord
{
    protected static string $resource = PembelianResource::class;

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
            $total = collect($data['pembelianDetail'] ?? [])
                ->sum(fn($item) => $item['sub_total_harga'] ?? 0);

            $pembayaranItems = collect($data['pembayaranPembelian'] ?? []);
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

            $statusPembelian = $totalPembayaran >= $total ? 'lunas' : 'belum lunas';
            if ($record->status_pembelian === 'diproses') {
                $statusPembelian = $record->status_pembelian;
            }

            $record->update([
                'id_pemasok' => $data['id_pemasok'],
                'total_harga' => $total,
                'status_pembelian' => $statusPembelian,
                'uang_diterima' => $totalPembayaran,
                'uang_kembalian' => $totalPembayaran > $total ? $totalPembayaran - $total : 0,
                'sisa_bayar' => $totalPembayaran < $total ? $total - $totalPembayaran : 0,
            ]);

            return $record;
        });
    }


    protected function mutateFormDataBeforeFill(array $data): array
    {
        $pembelian = Pembelian::with(['pembelianDetail', 'pembayaranPembelian.pembayaran'])
            ->find($this->record->id_pembelian);

        if (!$pembelian) {
            return $data;
        }

        foreach ($pembelian->pembayaranPembelian as $index => $pembayaranRelation) {
            $pembayaran = $pembayaranRelation->pembayaran;
            if ($pembayaran) {
                $data['pembayaranPembelian'][$index]['total_bayar'] = $pembayaran->total_bayar;
                $data['pembayaranPembelian'][$index]['jenis_pembayaran'] = $pembayaran->jenis_pembayaran;
                $data['pembayaranPembelian'][$index]['id_tipe_transfer'] = $pembayaran->id_tipe_transfer;
                $data['pembayaranPembelian'][$index]['keterangan'] = $pembayaran->keterangan;

                if ($pembayaran->id_tipe_transfer) {
                    $tipeTransfer = \App\Models\TipeTransfer::find($pembayaran->id_tipe_transfer);
                    if ($tipeTransfer) {
                        $data['pembayaranPembelian'][$index]['tipe_pembayaran'] = $tipeTransfer->metode_transfer;
                    }
                }
            }
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $pembelianDetail = $data['pembelianDetail'] ?? [];
        $totalHarga = 0;

        foreach ($pembelianDetail as $item) {
            $totalHarga += $item['sub_total_harga'] ?? 0;
        }

        $pembayaranItems = $data['pembayaranPembelian'] ?? [];
        $totalPembayaran = 0;

        foreach ($pembayaranItems as $item) {
            $totalPembayaran += $item['total_bayar'] ?? 0;
        }

        $data['total_harga'] = $totalHarga;
        $data['status_pembelian'] = $totalPembayaran >= $totalHarga ? 'lunas' : 'belum lunas';
        $data['uang_diterima'] = $totalPembayaran;
        $data['uang_kembalian'] = $totalPembayaran > $totalHarga ? $totalPembayaran - $totalHarga : 0;
        $data['sisa_bayar'] = $totalPembayaran < $totalHarga ? $totalHarga - $totalPembayaran : 0;

        return $data;
    }
}
