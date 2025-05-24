<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use App\Models\BayarZakat;
use App\Models\Pembayaran;
use App\Models\PenerimaZakat;
use App\Models\Penjualan;
use App\Models\TipeTransfer;
use Filament\Facades\Filament;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Illuminate\Support\Facades\DB;
use Filament\Actions\Action;

class CreatePembayaranZakat extends Page
{
    protected static string $resource = PembayaranZakatResource::class;
    protected static string $view = 'filament.resources.pembayaran-zakat-resource.pages.create-pembayaran-zakat';

    public ?array $data = [];

    private const ZAKAT_PERCENTAGE = 0.025;
    private const PAYMENT_TYPES = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer',
    ];
    private const TRANSFER_METHODS = [
        'bank' => 'Bank',
        'e-wallet' => 'E-wallet',
    ];

    public function mount(): void
    {
        $this->initializeData();
        $this->fillFormData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Pembayaran Zakat')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema($this->getFormFields())
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        try {
            $data = $this->form->getState();

            $this->validateRecordIds($data);
            $this->processZakatPayment($data);
            $this->showSuccessNotification();
            $this->redirectToIndex();
        } catch (\Exception $e) {
            $this->showErrorNotification($e->getMessage());
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')
                ->label('Bayar Zakat')
                ->submit('create'),
        ];
    }

    private function initializeData(): void
    {
        $recordIds = request()->query('recordIds', []);

        $this->data = [
            'recordIds' => $recordIds,
            'id_penerima_zakat' => null,
            'jenis_pembayaran' => null,
            'id_tipe_transfer' => null,
        ];

        if (!empty($recordIds)) {
            $this->calculateZakatAmounts($recordIds);
        } else {
            $this->setDefaultAmounts();
        }
    }

    private function calculateZakatAmounts(array $recordIds): void
    {
        $penjualans = $this->getPenjualanWithDetails($recordIds);
        $modalTerjual = $this->calculateModalTerjual($penjualans);

        $this->data['modal_terjual'] = (float) $modalTerjual;
        $this->data['nominal_zakat'] = (float) ($modalTerjual * self::ZAKAT_PERCENTAGE);
    }

    private function getPenjualanWithDetails(array $recordIds)
    {
        return Penjualan::with(['penjualanDetail.produk'])
            ->whereIn('id_penjualan', $recordIds)
            ->get();
    }

    private function calculateModalTerjual($penjualans): float
    {
        return $penjualans->sum(function ($penjualan) {
            return $penjualan->penjualanDetail->sum(function ($detail) {
                return optional($detail->produk)->harga_beli * $detail->jumlah_produk;
            });
        });
    }

    private function setDefaultAmounts(): void
    {
        $this->data['modal_terjual'] = 0;
        $this->data['nominal_zakat'] = 0;
    }

    private function fillFormData(): void
    {
        $this->form->fill([
            'recordIds' => $this->data['recordIds'],
            'modal_terjual' => $this->data['modal_terjual'],
            'nominal_zakat' => $this->data['nominal_zakat'],
        ]);
    }

    private function getFormFields(): array
    {
        return [
            $this->getHiddenRecordIdsField(),
            $this->getPenerimaZakatField(),
            $this->getJenisPembayaranField(),
            $this->getMetodeTransferField(),
            $this->getTipeTransferField(),
            $this->getModalTerjualField(),
            $this->getNominalZakatField(),
        ];
    }

    private function getHiddenRecordIdsField(): Components\Hidden
    {
        return Components\Hidden::make('recordIds')
            ->default($this->data['recordIds'] ?? []);
    }

    private function getPenerimaZakatField(): Components\Select
    {
        return Components\Select::make('id_penerima_zakat')
            ->label('Nama Penerima Zakat')
            ->options(PenerimaZakat::pluck('nama_penerima', 'id_penerima_zakat'))
            ->required()
            ->searchable()
            ->live();
    }

    private function getJenisPembayaranField(): Components\Select
    {
        return Components\Select::make('jenis_pembayaran')
            ->label('Metode Pembayaran')
            ->options(self::PAYMENT_TYPES)
            ->afterStateUpdated(function ($state, $set) {
                if ($state === 'tunai') {
                    $set('id_tipe_transfer', null);
                    $set('metode_transfer', null);
                }
            })
            ->required()
            ->reactive();
    }

    private function getMetodeTransferField(): Components\Select
    {
        return Components\Select::make('metode_transfer')
            ->label('Metode Transfer')
            ->options(self::TRANSFER_METHODS)
            ->dehydrated(false)
            ->visible(fn($get) => $get('jenis_pembayaran') === 'transfer')
            ->reactive()
            ->searchable();
    }

    private function getTipeTransferField(): Components\Select
    {
        return Components\Select::make('id_tipe_transfer')
            ->label('Jenis Transfer')
            ->options(fn($get) => $this->getTipeTransferOptions($get('metode_transfer')))
            ->visible(fn($get) => $this->shouldShowTipeTransfer($get))
            ->searchable()
            ->reactive();
    }

    private function getTipeTransferOptions(?string $metodeTransfer): array
    {
        if (!$metodeTransfer) {
            return [];
        }

        return TipeTransfer::where('metode_transfer', $metodeTransfer)
            ->pluck('jenis_transfer', 'id_tipe_transfer')
            ->toArray();
    }

    private function shouldShowTipeTransfer($get): bool
    {
        return $get('jenis_pembayaran') === 'transfer' &&
            in_array($get('metode_transfer'), ['bank', 'e-wallet']);
    }

    private function getModalTerjualField(): Components\TextInput
    {
        return Components\TextInput::make('modal_terjual')
            ->label('Total Modal')
            ->disabled()
            ->dehydrated()
            ->default(0)
            ->formatStateUsing(fn($state) => $this->formatCurrency($state))
            ->afterStateHydrated(
                fn($component, $state) =>
                $component->state($this->formatCurrency($state))
            );
    }

    private function getNominalZakatField(): Components\TextInput
    {
        return Components\TextInput::make('nominal_zakat')
            ->label('Total Zakat (2.5%)')
            ->disabled()
            ->dehydrated()
            ->default(0)
            ->formatStateUsing(fn($state) => $this->formatCurrency($state, 'Rp '))
            ->afterStateHydrated(
                fn($component, $state) =>
                $component->state($this->formatCurrency($state, 'Rp '))
            );
    }

    private function validateRecordIds(array $data): void
    {
        if (empty($data['recordIds'])) {
            throw new \Exception('Tidak ada transaksi yang dipilih');
        }
    }

    private function processZakatPayment(array $data): void
    {
        $calculatedData = $this->ensureCalculatedAmounts($data);
        $modalTerjual = $this->parseNumericValue($calculatedData['modal_terjual']);
        $nominalZakat = $this->parseNumericValue($calculatedData['nominal_zakat']);

        DB::transaction(function () use ($calculatedData, $modalTerjual, $nominalZakat) {
            $pembayaran = $this->createPembayaran($calculatedData, $nominalZakat);
            $bayarZakat = $this->createBayarZakat($calculatedData, $pembayaran, $modalTerjual);
            $this->updatePenjualanRecords($calculatedData['recordIds'], $bayarZakat);
        });
    }

    private function ensureCalculatedAmounts(array $data): array
    {
        if (!isset($data['modal_terjual']) || !isset($data['nominal_zakat'])) {
            $penjualans = $this->getPenjualanWithDetails($data['recordIds']);
            $data['modal_terjual'] = $this->calculateModalTerjual($penjualans);
            $data['nominal_zakat'] = $data['modal_terjual'] * self::ZAKAT_PERCENTAGE;
        }

        return $data;
    }

    private function parseNumericValue($value): float
    {
        return (float) preg_replace('/[^0-9]/', '', $value);
    }

    private function createPembayaran(array $data, float $nominalZakat): Pembayaran
    {
        return Pembayaran::create([
            'total_bayar' => $nominalZakat,
            'keterangan' => $data['keterangan'] ?? 'Pembayaran Zakat',
            'jenis_pembayaran' => $data['jenis_pembayaran'],
            'id_tipe_transfer' => $this->getTipeTransferId($data),
        ]);
    }

    private function getTipeTransferId(array $data): ?int
    {
        return $data['jenis_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null;
    }

    private function createBayarZakat(array $data, Pembayaran $pembayaran, float $modalTerjual): BayarZakat
    {
        $userId = Filament::auth()->id();
        $idBayarZakat = $this->generateBayarZakatId($userId);

        return BayarZakat::create([
            'id_bayar_zakat' => $idBayarZakat,
            'id_pemilik' => Filament::auth()->id(),
            'id_penerima_zakat' => $data['id_penerima_zakat'],
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'modal_terjual' => $modalTerjual,
        ]);
    }

    private function updatePenjualanRecords(array $recordIds, BayarZakat $bayarZakat): void
    {
        Penjualan::whereIn('id_penjualan', $recordIds)
            ->update(['id_bayar_zakat' => $bayarZakat->id_bayar_zakat]);
    }

    private function showSuccessNotification(): void
    {
        Notification::make()
            ->title('Berhasil')
            ->body('Pembayaran zakat telah disimpan')
            ->success()
            ->send();
    }

    private function showErrorNotification(string $message): void
    {
        Notification::make()
            ->title('Error')
            ->body('Terjadi kesalahan: ' . $message)
            ->danger()
            ->send();
    }

    private function redirectToIndex(): void
    {
        $this->redirect(PembayaranZakatResource::getUrl('index'), navigate: true);
    }

    private function formatCurrency($state, string $prefix = 'Rp. '): string
    {
        $value = is_numeric($state) ? $state : 0;
        return $prefix . number_format($value, 0, ',', '.');
    }

    private function generateBayarZakatId(int $userId): string
    {
        $today = now();
        $date = $today->format('Ymd'); // 20250525

        $count = BayarZakat::whereDate('created_at', $today->toDateString())
            ->where('id_pemilik', $userId)
            ->count();

        $increment = str_pad($count + 1, 2, '0', STR_PAD_LEFT); // 01, 02, 03, etc.

        return "ZKT-{$userId}{$date}{$increment}";
    }
}
