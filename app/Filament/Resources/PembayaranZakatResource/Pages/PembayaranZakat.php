<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use App\Models\Penjualan;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class PembayaranZakat extends Page implements HasForms
{
    use InteractsWithForms;
    protected static string $resource = PembayaranZakatResource::class;

    protected static string $view = 'filament.resources.pembayaran-zakat-resource.pages.pembayaran-zakat';

    public ?array $recordIds = [];

    public float $totalModal = 0;
    public float $totalZakat = 0;

    protected function makeForm(): \Filament\Forms\Form
    {
        return $this->form()
            ->schema($this->getFormSchema());
    }

    public function mount(): void
    {
        $this->recordIds = request()->query('recordIds', []);

        $penjualans = Penjualan::with('penjualanDetail.produk')
            ->whereIn('id_penjualan', $this->recordIds)
            ->get();

        $this->totalModal = $penjualans->sum(fn ($penjualan) => $penjualan->modal_terjual);
        $this->totalZakat = $penjualans->sum(fn ($penjualan) => $penjualan->zakat);

        $this->makeForm();

        $this->form->fill([
            'modal_terjual' => $this->totalModal,
            'nominal_zakat' => $this->totalZakat,
        ]);
    }

    protected function getFormSchema(): array
    {
        return [
            Select::make('id_penerima_zakat')
                ->label('Nama Penerima Zakat')
                ->options(\App\Models\PenerimaZakat::all()->pluck('nama_penerima', 'id_penerima_zakat'))
                ->required(),

            Select::make('id_metode_pembayaran')
                ->label('Metode Pembayaran')
                ->options([
                    'tunai' => 'Tunai',
                    'transfer' => 'Transfer',
                ])
                ->required(),

            TextInput::make('modal_terjual')
                ->label('Total Modal')
                // ->default(fn () => $this->totalModal)
                ->disabled()
                ->formatStateUsing(fn ($state) => 'Rp. ' . number_format($state ?? 0, 0, ',', '.')),

            TextInput::make('nominal_zakat')
                ->label('Total Zakat (2.5%)')
                // ->default(fn () => $this->totalZakat)
                ->disabled()
                ->formatStateUsing(fn ($state) => 'Rp. ' . number_format($state ?? 0, 0, ',', '.')),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['modal_terjual'] = $this->totalModal;
        $data['nominal_zakat'] = $this->totalZakat;
        $data['tanggal_bayar'] = now();
        $data['id_pemilik'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        Penjualan::whereIn('id_penjualan', $this->recordIds)->update([
            'id_bayar_zakat' => $this->record->id_bayar_zakat,
        ]);

        Notification::make()
            ->title('Pembayaran Zakat berhasil')
            ->success()
            ->send();
    }

    // Tambahin ini biar form-nya ke-render
    public function getContent(): string|\Illuminate\Contracts\View\View
    {
        return (string) $this->form;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
