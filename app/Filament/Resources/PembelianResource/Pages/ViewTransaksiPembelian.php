<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Enums\StatusTransaksiPembelian;
use App\Filament\Resources\PembelianResource;
use App\Filament\Resources\PembelianResource\RelationManagers\PembayaranRelationManager;
use App\Filament\Resources\PembelianResource\RelationManagers\ProdukRelationManager;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;
use App\Models\Stok;
use App\Models\Pembayaran;
use App\Models\PembayaranPembelian;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Filament\Forms;

class ViewTransaksiPembelian extends ViewRecord
{
    protected static string $resource = PembelianResource::class;

    public ?array $pembayaranData = [];

    public function getHeaderActions(): array
    {
        $status = $this->record->status_pembelian;
        $actions = [];

        if ($status === StatusTransaksiPembelian::Diproses) {
            $actions[] = Actions\Action::make('konfirmasiPembelian')
                ->label('Konfirmasi Pembelian')
                ->action(function () {
                    DB::beginTransaction();

                    try {
                        $pembelian = $this->record;

                        if (!$pembelian) {
                            throw new \Exception('Data pembelian tidak ditemukan.');
                        }

                        if ($pembelian->pembelianDetail->isEmpty()) {
                            throw new \Exception('Detail pembelian tidak ditemukan.');
                        }

                        foreach ($pembelian->pembelianDetail as $detail) {
                            if (!$detail->id_produk) {
                                throw new \Exception('ID produk tidak valid pada detail pembelian.');
                            }

                            if ($detail->jumlah_produk <= 0) {
                                throw new \Exception('Jumlah produk tidak valid untuk produk ID: ' . $detail->id_produk);
                            }

                            Stok::create([
                                'id_produk' => $detail->id_produk,
                                'jumlah_stok' => $detail->jumlah_produk,
                                'jenis_stok' => 'In',
                                'jenis_transaksi' => 'Pembelian',
                                'keterangan' => 'Stok masuk dari pembelian #' . $pembelian->id_pembelian,
                            ]);
                        }

                        $status = $pembelian->uang_diterima >= $pembelian->total_harga ? 'lunas' : 'belum lunas';

                        $pembelian->update([
                            'status_pembelian' => $status,
                        ]);

                        DB::commit();

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pembelian berhasil dikonfirmasi dan stok ditambahkan.')
                            ->success()
                            ->send();

                        $this->redirect(PembelianResource::getUrl('index'));
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        report($e);

                        Notification::make()
                            ->title('Gagal')
                            ->body('Terjadi kesalahan saat konfirmasi pembelian: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->color('primary');
        }

        if ($status === StatusTransaksiPembelian::BelumLunas) {
            $actions[] = Actions\Action::make('bayarSekarang')
                ->label('Bayar Sekarang')
                ->form([
                    Forms\Components\Group::make(PembelianResource::getPembayaranFormSchema())
                        ->columnSpanFull(),
                    Forms\Components\Placeholder::make('sisa_pembayaran')
                        ->label('Sisa Pembayaran')
                        ->content(fn() => 'Rp. ' . number_format($this->record->sisa_bayar, 0, ',', '.')),
                    Forms\Components\TextInput::make('keterangan')
                        ->label('Keterangan')
                        ->default('Pembayaran untuk pembelian #' . $this->record->id_pembelian)
                        ->placeholder('Masukkan keterangan pembayaran')
                        ->maxLength(255),
                ])
                ->action(function (array $data): void {
                    DB::beginTransaction();

                    try {
                        $pembelian = $this->record;

                        if (!isset($data['nominal']) || $data['nominal'] <= 0) {
                            throw new \Exception('Nominal pembayaran harus lebih dari 0.');
                        }

                        $metode = $data['metode_pembayaran'] ?? 'tunai';
                        $tipe = ($metode === 'transfer') ? ($data['tipe_pembayaran'] ?? null) : null;
                        $idTipe = ($metode === 'transfer') ? ($data['id_tipe_transfer'] ?? null) : null;
                        $keterangan = $data['keterangan'] ?? 'Pembayaran untuk pembelian #' . $pembelian->id_pembelian;

                        $pembayaran = Pembayaran::create([
                            'total_bayar' => $data['nominal'],
                            'jenis_pembayaran' => $metode,
                            'tipe_pembayaran' => $tipe,
                            'id_tipe_transfer' => $idTipe,
                            'keterangan' => $keterangan,
                        ]);

                        PembayaranPembelian::create([
                            'id_pembelian' => $pembelian->id_pembelian,
                            'id_pembayaran' => $pembayaran->id_pembayaran,
                        ]);

                        $status = $pembelian->uang_diterima >= $pembelian->total_harga ? 'lunas' : 'belum lunas';

                        $pembelian->update([
                            'status_pembelian' => $status,
                        ]);

                        DB::commit();

                        Notification::make()
                            ->title('Berhasil')
                            ->body('Pembayaran berhasil disimpan.')
                            ->success()
                            ->send();

                        $this->redirect(PembelianResource::getUrl('view', ['record' => $pembelian->id_pembelian]));
                    } catch (\Throwable $e) {
                        DB::rollBack();
                        report($e);

                        Notification::make()
                            ->title('Gagal')
                            ->body('Terjadi kesalahan saat menyimpan pembayaran: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->color('danger')
                ->modalHeading('Pembayaran Pembelian')
                ->modalDescription('Masukkan detail pembayaran untuk pembelian ini.');
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
