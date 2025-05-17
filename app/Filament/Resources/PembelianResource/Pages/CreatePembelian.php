<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Models\Pembayaran;
use App\Models\PembayaranPembelian;
use Filament\Actions;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;

class CreatePembelian extends CreateRecord
{
    use HasWizard;
    protected static string $resource = PembelianResource::class;
    protected ?int $pembayaranId = null;

    public function form(Form $form): Form
    {
        return parent::form($form)
            ->schema([
                Wizard::make($this->getSteps())
                    ->startOnStep($this->getStartStep())
                    ->cancelAction($this->getCancelFormAction())
                    ->submitAction($this->getSubmitFormAction())
                    ->skippable($this->hasSkippableSteps())
                    ->contained(false),
            ])
            ->columns(null);
    }

    // protected function beforeCreate(): void
    // {
    //     $totalHarga = $this->record->pembelianDetail->sum(function ($detail) {
    //         $hargaBeli = $detail->produk?->harga_beli ?? 0;
    //         $jumlah = $detail->jumlah_produk ?? 0;
    //         return $hargaBeli * $jumlah;
    //     });

    //     $this->record->update([
    //         'total_harga' => $totalHarga,
    //     ]);
    // }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ambil data pembayaran
        $pembayaranData = [
            'total_bayar' => $data['nominal'],
            'jenis_pembayaran' => $data['metode_pembayaran'],
            'id_tipe_transfer' => $data['metode_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null,
            'keterangan' => 'Pembayaran Pembelian', // bisa diubah jika perlu
        ];

        // Simpan pembayaran
        $pembayaran = Pembayaran::create($pembayaranData);

        // Simpan ID pembayaran ke properti supaya bisa dipakai setelah pembelian disimpan
        $this->pembayaranId = $pembayaran->id;

        // Hapus field yang tidak ada di tabel pembelian (agar tidak error)
        unset($data['nominal'], $data['metode_pembayaran'], $data['tipe_pembayaran'], $data['id_tipe_transfer']);

        return $data;
    }

    protected function created(): void
    {
        PembayaranPembelian::create([
            'id_pembelian' => $this->record->id_pembelian,
            'id_pembayaran' => $this->pembayaranId,
        ]);
    }

    protected function getSteps(): array
    {
        return [
            Step::make('Pemasok')
                ->schema([
                    Section::make()->schema(PembelianResource::getDetailsFormSchema())->columns(),
                ]),

            Step::make('Produk')
                ->schema([
                    Section::make()->schema([
                        PembelianResource::getProdukRepeater(),
                    ]),
                ]),

            Step::make('Pembayaran')
                ->schema([
                    Section::make()->schema(PembelianResource::getPembayaranFormSchema())->columns(),
                ]),
        ];
    }
}
