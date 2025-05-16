<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use App\Models\BayarZakat;
use App\Models\TipeTransfer;
use App\Models\PenerimaZakat;
use App\Models\Penjualan;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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
                \Filament\Forms\Components\Hidden::make('recordIds')
                    ->default($this->data['recordIds'] ?? []),

                Select::make('id_penerima_zakat')
                    ->label('Nama Penerima Zakat')
                    ->options(PenerimaZakat::all()->pluck('nama_penerima', 'id_penerima_zakat'))
                    ->required()
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        logger()->debug('Penerima zakat updated:', ['state' => $state]);
                    }),

                Select::make("jenis_pembayaran")
                    ->label('Jenis Pembayaran')
                    ->options([
                        'tunai' => 'Tunai',
                        'transfer' => 'Transfer',
                    ])
                    ->required(),
                Select::make('id_tipe_transfer')
                    ->label('Tipe Transfer')
                    ->options(TipeTransfer::get()->mapWithKeys(function ($item) {
                        if ($item->metode_transfer) {
                            // logger()->info('TipeTransfer ID: ' . $item->id_tipe_transfer);
                            $label = ucfirst($item->metode_transfer);
                            $label .= ' - ' . $item->jenis_transfer;
                        } else {
                            $label = 'Tidak diketahui';
                        }

                        return [$item->id_tipe_transfer => $label];
                    }))
                    // ->required() 
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        logger()->debug('Metode pembayaran updated:', ['state' => $state]);
                    }),

                TextInput::make('modal_terjual')
                    ->label('Total Modal')
                    ->disabled()
                    ->dehydrated()
                    ->default(0)
                    ->formatStateUsing(function ($state) {
                        $value = is_numeric($state) ? $state : 0;
                        return 'Rp ' . number_format($value, 0, ',', '.');
                    })
                    ->afterStateHydrated(function ($component, $state) {
                        // Simpan nilai numerik tapi tampilkan yang sudah diformat
                        $component->state('Rp ' . number_format(is_numeric($state) ? $state : 0, 0, ',', '.'));
                    }),

                TextInput::make('nominal_zakat')
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
                    })
            ])
            ->statePath('data');
    }

    public function create()
    {
        try {
            $data = $this->form->getState();
            logger()->debug('Form data before validation:', $data);

            // Validate required fields
            if (empty($data['recordIds'])) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada transaksi yang dipilih')
                    ->danger()
                    ->send();
                return;
            }

            // Validate required fields
            if (empty($data['recordIds'])) {
                Notification::make()
                    ->title('Error')
                    ->body('Tidak ada transaksi yang dipilih')
                    ->danger()
                    ->send();
                return;
            }

            // Recalculate totals if needed
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

            // Remove currency formatting before saving
            $data['modal_terjual'] = (float) preg_replace('/[^0-9]/', '', $data['modal_terjual']);
            $data['nominal_zakat'] = (float) preg_replace('/[^0-9]/', '', $data['nominal_zakat']);

            // Create BayarZakat record
            $bayarZakat = BayarZakat::create([
                'id_metode_pembayaran' => $data['id_metode_pembayaran'],
                'id_pemilik' => Filament::auth()->id(),
                'id_penerima_zakat' => $data['id_penerima_zakat'],
                'modal_terjual' => $data['modal_terjual'],
                'nominal_zakat' => $data['nominal_zakat'],
                'tanggal_bayar' => now(),
            ]);

            // Update Penjualan records
            Penjualan::whereIn('id_penjualan', $data['recordIds'])
                ->update(['id_bayar_zakat' => $bayarZakat->id_bayar_zakat]);

            // Redirect with success message
            Notification::make()
                ->title('Berhasil')
                ->body('Pembayaran zakat telah disimpan')
                ->success()
                ->send();

            $this->redirect(
                PembayaranZakatResource::getUrl('index'),
                navigate: true
            );
        } catch (\Exception $e) {
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
