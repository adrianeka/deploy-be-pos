<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use App\Models\BayarZakat;
use App\Models\PenerimaZakat;
use App\Models\Penjualan;
use Filament\Facades\Filament;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class CreatePembayaranZakat extends Page
{
    protected static string $resource = PembayaranZakatResource::class;
    protected static string $view = 'filament.resources.pembayaran-zakat-resource.pages.create-pembayaran-zakat';

    public ?array $data = [];

    public function mount(): void
    {
        // Get record IDs from URL
        $recordIds = request()->query('recordIds', []);

        // Store recordIds in component state
        $this->data['recordIds'] = $recordIds;
        $this->data['id_penerima_zakat'] = null;
        $this->data['id_metode_pembayaran'] = null;

        if (!empty($recordIds)) {
            // Load penjualan records with their details
            $penjualans = Penjualan::with(['penjualanDetail.produk'])
                ->whereIn('id_penjualan', $recordIds)
                ->get();

            // Calculate totals
            $modalTerjual = $penjualans->sum(function ($penjualan) {
                return $penjualan->penjualanDetail->sum(function ($detail) {
                    return optional($detail->produk)->harga_beli * $detail->jumlah_produk;
                });
            });

            $nominalZakat = $modalTerjual * 0.025;

            // Store the values in component state
            $this->data['modal_terjual'] = (float)$modalTerjual;
            $this->data['nominal_zakat'] = (float)$nominalZakat;
        } else {
            $this->data['modal_terjual'] = 0;
            $this->data['nominal_zakat'] = 0;
        }
        $this->form->fill([
            'recordIds' => $this->data['recordIds'],
            'modal_terjual' => $this->data['modal_terjual'],
            'nominal_zakat' => $this->data['nominal_zakat'],
        ]);
        logger()->debug('Form initialized with:', $this->data);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Kasir')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\Hidden::make('recordIds')
                                    ->default($this->data['recordIds'] ?? []),

                                Components\Select::make('id_penerima_zakat')
                                    ->label('Nama Penerima Zakat')
                                    ->options(PenerimaZakat::all()->pluck('nama_penerima', 'id_penerima_zakat'))
                                    ->required()
                                    ->searchable()
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        logger()->debug('Penerima zakat updated:', ['state' => $state]);
                                    }),

                                Components\Select::make("jenis_pembayaran")
                                    ->label('Metode Pembayaran')
                                    ->options([
                                        'tunai' => 'Tunai',
                                        'transfer' => 'Transfer',
                                    ])
                                    ->afterStateUpdated(function ($state, $set) {
                                        if ($state === 'tunai') {
                                            $set('id_tipe_transfer', null);
                                            $set('jenis_transfer', null);
                                        }
                                    })
                                    ->required()
                                    ->reactive(),

                                Components\Select::make('metode_transfer')
                                    ->label('Metode Transfer')
                                    ->options([
                                        'bank' => 'Bank',
                                        'e-money' => 'E-Money',
                                    ])
                                    ->dehydrated(false)
                                    ->visible(fn($get) => $get('jenis_pembayaran') === 'transfer')
                                    ->reactive()
                                    ->searchable(),

                                Components\Select::make('id_tipe_transfer')
                                    ->label('Jenis Transfer')
                                    ->options(
                                        fn($get) =>
                                        \App\Models\TipeTransfer::query()
                                            ->where('metode_transfer', $get('metode_transfer'))
                                            ->pluck('jenis_transfer', 'id_tipe_transfer') // <- pakai ID sebagai key
                                    )
                                    ->visible(
                                        fn($get) =>
                                        $get('jenis_pembayaran') === 'transfer' &&
                                            in_array($get('metode_transfer'), ['bank', 'e-money'])
                                    )
                                    ->searchable()
                                    ->reactive(),

                                Components\TextInput::make('modal_terjual')
                                    ->label('Total Modal')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->formatStateUsing(function ($state) {
                                        $value = is_numeric($state) ? $state : 0;
                                        return 'Rp ' . number_format($value, 0, ',', '.');
                                    })
                                    ->afterStateHydrated(function ($component, $state) {
                                        $component->state('Rp ' . number_format(is_numeric($state) ? $state : 0, 0, ',', '.'));
                                    }),

                                Components\TextInput::make('nominal_zakat')
                                    ->label('Total Zakat (2.5%)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->default(0)
                                    ->formatStateUsing(function ($state) {
                                        $value = is_numeric($state) ? $state : 0;
                                        return 'Rp ' . number_format($value, 0, ',', '.');
                                    })
                                    ->afterStateHydrated(function ($component, $state) {
                                        $component->state('Rp ' . number_format(is_numeric($state) ? $state : 0, 0, ',', '.'));
                                    }),
                            ])
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function create()
    {
        try {
            $data = $this->form->getState();
            logger()->debug('Form data before validation:', $data);

            // Validasi recordIds wajib ada dan tidak kosong
            if (empty($data['recordIds']) || count($data['recordIds']) === 0) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada transaksi yang dipilih')
                    ->danger()
                    ->send();
                return;
            }

            // Hitung ulang modal_terjual dan nominal_zakat jika belum ada
            if (!isset($data['modal_terjual']) || !isset($data['nominal_zakat'])) {
                $penjualans = Penjualan::with(['penjualanDetail.produk'])
                    ->whereIn('id_penjualan', $data['recordIds'])
                    ->get();

                $data['modal_terjual'] = $penjualans->sum(function ($penjualan) {
                    return $penjualan->penjualanDetail->sum(function ($detail) {
                        return optional($detail->produk)->harga_beli * $detail->jumlah_produk;
                    });
                });

                $data['nominal_zakat'] = $data['modal_terjual'] * 0.025;
            }

            // Hilangkan format "Rp " dan non-digit dari modal dan nominal zakat
            $data['modal_terjual'] = (float) preg_replace('/[^0-9]/', '', $data['modal_terjual']);
            $data['nominal_zakat'] = (float) preg_replace('/[^0-9]/', '', $data['nominal_zakat']);

            // Buat record BayarZakat
            $bayarZakat = BayarZakat::create([
                'id_pemilik' => Filament::auth()->id(),
                'id_penerima_zakat' => $data['id_penerima_zakat'],
                'jenis_pembayaran' => $data['jenis_pembayaran'], // tunai / transfer
                'id_tipe_transfer' => $data['jenis_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null,
                'modal_terjual' => $data['modal_terjual'],
                'nominal_zakat' => $data['nominal_zakat'],
            ]);

            // Update Penjualan dengan id_bayar_zakat baru
            Penjualan::whereIn('id_penjualan', $data['recordIds'])
                ->update(['id_bayar_zakat' => $bayarZakat->id_bayar_zakat]);

            // Notifikasi sukses
            Notification::make()
                ->title('Berhasil')
                ->body('Pembayaran zakat telah disimpan')
                ->success()
                ->send();

            // Redirect ke halaman index resource
            $this->redirect(PembayaranZakatResource::getUrl('index'), navigate: true);
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
