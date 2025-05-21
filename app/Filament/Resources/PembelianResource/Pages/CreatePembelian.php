<?php

namespace App\Filament\Resources\PembelianResource\Pages;

use App\Filament\Resources\PembelianResource;
use App\Models\Pembayaran;
use App\Models\PembayaranPembelian;
use App\Models\Pembelian;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Wizard;
use Filament\Forms\Components\Wizard\Step;
use Filament\Forms\Form;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\CreateRecord\Concerns\HasWizard;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;

class CreatePembelian extends CreateRecord
{
    use HasWizard;

    protected static string $resource = PembelianResource::class;

    public function form(Form $form): Form
    {
        return $form
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

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(function () use ($data) {
            $total = collect($data['pembelianDetail'] ?? [])
                ->sum(fn($item) => $item['sub_total_harga'] ?? 0);

            $pembelian = Pembelian::create([
                'id_pemasok' => $data['id_pemasok'],
                'total_harga' => $total,
                'status_pembelian' => 'diproses',
            ]);

            $pembayaran = Pembayaran::create([
                'total_bayar' => $data['nominal'] ?? 0,
                'jenis_pembayaran' => $data['metode_pembayaran'],
                'id_tipe_transfer' => $data['metode_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null,
                'keterangan' => 'Pembayaran Pembelian',
            ]);

            PembayaranPembelian::create([
                'id_pembelian' => $pembelian->id_pembelian,
                'id_pembayaran' => $pembayaran->id_pembayaran,
            ]);

            return $pembelian;
        });
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
