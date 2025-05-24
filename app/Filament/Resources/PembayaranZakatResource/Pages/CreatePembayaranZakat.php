<?php

namespace App\Filament\Resources\PembayaranZakatResource\Pages;

use App\Filament\Resources\PembayaranZakatResource;
use App\Models\{BayarZakat, Pembayaran, PenerimaZakat, Penjualan, TipeTransfer};
use Filament\{Actions\Action, Facades\Filament, Forms\Components, Forms\Form, Notifications\Notification, Resources\Pages\Page};
use Illuminate\Support\Facades\DB;

class CreatePembayaranZakat extends Page
{
    protected static string $resource = PembayaranZakatResource::class;
    protected static string $view = 'filament.resources.pembayaran-zakat-resource.pages.create-pembayaran-zakat';

    public ?array $data = [];

    private const ZAKAT_PERCENTAGE = 0.025;

    public function mount(): void
    {
        $this->initData();
        $this->fillFormData();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Pembayaran Zakat')
                    ->schema([
                        Components\Grid::make(2)->schema($this->getFormFields())
                    ])
                    ->collapsible()
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        try {
            $data = $this->form->getState();
            $this->validateRecordIds($data);
            $this->handleZakatPayment($data);

            $this->notifySuccess();
            $this->redirectToIndex();
        } catch (\Exception $e) {
            $this->notifyError($e->getMessage());
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('create')->label('Bayar Zakat')->submit('create'),
        ];
    }

    // -- Initialization --

    private function initData(): void
    {
        $recordIds = request()->query('recordIds', []);

        $this->data = [
            'recordIds' => $recordIds,
            'id_penerima_zakat' => null,
            'jenis_pembayaran' => null,
            'id_tipe_transfer' => null,
        ];

        if ($recordIds) {
            $penjualans = $this->getPenjualanDetails($recordIds);
            $modal = $this->calculateModal($penjualans);
            $this->data['modal_terjual'] = $modal;
            $this->data['nominal_zakat'] = $modal * self::ZAKAT_PERCENTAGE;
        } else {
            $this->data['modal_terjual'] = 0;
            $this->data['nominal_zakat'] = 0;
        }
    }

    private function fillFormData(): void
    {
        $this->form->fill([
            'recordIds' => $this->data['recordIds'],
            'modal_terjual' => $this->data['modal_terjual'],
            'nominal_zakat' => $this->data['nominal_zakat'],
        ]);
    }

    // -- Form Fields --

    private function getFormFields(): array
    {
        return [
            Components\Hidden::make('recordIds')
                ->default($this->data['recordIds'] ?? []),

            Components\Hidden::make('modal_terjual'),

            Components\Hidden::make('nominal_zakat'),

            Components\Placeholder::make('modal_terjual_display')
                ->label('Modal Terjual')
                ->content(fn(callable $get) => 'Rp. ' . number_format($get('modal_terjual') ?? 0, 0, ',', '.')),

            Components\Placeholder::make('nominal_zakat_display')
                ->label('Total Zakat (2.5%)')
                ->content(fn(callable $get) => 'Rp. ' . number_format($get('nominal_zakat') ?? 0, 0, ',', '.')),

            Components\Placeholder::make('norek_display')
                ->label('Nomor Rekening Penerima Zakat')
                ->visible(fn($get) => $get('jenis_pembayaran') === 'transfer')
                ->content(function (callable $get) {
                    $idPenerima = $get('id_penerima_zakat');
                    $noRek = \App\Models\PenerimaZakat::find($idPenerima)?->no_rekening;

                    if (!$noRek) {
                        return '-';
                    }

                    return trim(chunk_split(preg_replace('/\s+/', '', $noRek), 4, ' '));
                })
                ->columnSpanFull(),


            Components\Select::make('id_penerima_zakat')
                ->label('Nama Penerima Zakat')
                ->options(PenerimaZakat::pluck('nama_penerima', 'id_penerima_zakat'))
                ->required()
                ->searchable()
                ->live(),

            Components\Select::make('jenis_pembayaran')
                ->label('Metode Pembayaran')
                ->options(['tunai' => 'Tunai', 'transfer' => 'Transfer'])
                ->required()
                ->reactive()
                ->visible(fn($get) => $get('id_penerima_zakat') !== null)
                ->afterStateUpdated(fn($state, $set) => $state === 'tunai' ? $set('id_tipe_transfer', null) : null),

            Components\Select::make('metode_transfer')
                ->label('Metode Transfer')
                ->options(['bank' => 'Bank', 'e-wallet' => 'E-wallet'])
                ->visible(fn($get) => $get('jenis_pembayaran') === 'transfer')
                ->dehydrated(false)
                ->searchable()
                ->reactive()
                ->required(),

            Components\Select::make('id_tipe_transfer')
                ->label('Jenis Transfer')
                ->options(fn($get) => $this->getTipeTransferOptions($get('metode_transfer')))
                ->visible(fn($get) => $get('jenis_pembayaran') === 'transfer' && in_array($get('metode_transfer'), ['bank', 'e-wallet']))
                ->searchable()
                ->reactive()
                ->required(),
        ];
    }

    // -- Business Logic --

    private function validateRecordIds(array $data): void
    {
        if (empty($data['recordIds'])) {
            throw new \Exception('Tidak ada transaksi yang dipilih.');
        }
    }

    private function handleZakatPayment(array $data): void
    {
        $data = $this->ensureZakatIsCalculated($data);
        $modal = $this->parseCurrency($data['modal_terjual']);
        $zakat = $this->parseCurrency($data['nominal_zakat']);

        DB::transaction(function () use ($data, $modal, $zakat) {
            $pembayaran = $this->createPembayaran($data, $zakat);
            $bayarZakat = $this->createBayarZakat($data, $pembayaran, $modal);
            $this->linkPenjualanToBayarZakat($data['recordIds'], $bayarZakat);
        });
    }

    private function ensureZakatIsCalculated(array $data): array
    {
        if (!isset($data['modal_terjual']) || !isset($data['nominal_zakat'])) {
            $penjualans = $this->getPenjualanDetails($data['recordIds']);
            $modal = $this->calculateModal($penjualans);
            $data['modal_terjual'] = $modal;
            $data['nominal_zakat'] = $modal * self::ZAKAT_PERCENTAGE;
        }

        return $data;
    }

    // -- Database Operations --

    private function getPenjualanDetails(array $recordIds)
    {
        return Penjualan::with('penjualanDetail.produk')
            ->whereIn('id_penjualan', $recordIds)
            ->get();
    }

    private function calculateModal($penjualans): float
    {
        return $penjualans->sum(
            fn($p) =>
            $p->penjualanDetail->sum(
                fn($d) =>
                optional($d->produk)->harga_beli * $d->jumlah_produk
            )
        );
    }

    private function createPembayaran(array $data, float $total): Pembayaran
    {
        return Pembayaran::create([
            'total_bayar' => $total,
            'keterangan' => $data['keterangan'] ?? 'Pembayaran Zakat',
            'jenis_pembayaran' => $data['jenis_pembayaran'],
            'id_tipe_transfer' => $data['jenis_pembayaran'] === 'transfer' ? $data['id_tipe_transfer'] : null,
        ]);
    }

    private function createBayarZakat(array $data, Pembayaran $pembayaran, float $modal): BayarZakat
    {
        $userId = Filament::auth()->user()?->pemilik?->id_pemilik;
        return BayarZakat::create([
            'id_bayar_zakat' => $this->generateBayarZakatId($userId),
            'id_pemilik' => $userId,
            'id_penerima_zakat' => $data['id_penerima_zakat'],
            'id_pembayaran' => $pembayaran->id_pembayaran,
            'modal_terjual' => $modal,
        ]);
    }

    private function linkPenjualanToBayarZakat(array $recordIds, BayarZakat $bayarZakat): void
    {
        Penjualan::whereIn('id_penjualan', $recordIds)
            ->update(['id_bayar_zakat' => $bayarZakat->id_bayar_zakat]);
    }

    // -- Utilities --

    private function getTipeTransferOptions(?string $metode): array
    {
        return $metode
            ? TipeTransfer::where('metode_transfer', $metode)
            ->pluck('jenis_transfer', 'id_tipe_transfer')
            ->toArray()
            : [];
    }

    private function formatCurrency($value, string $prefix = 'Rp. '): string
    {
        return $prefix . number_format((float) $value, 0, ',', '.');
    }

    private function parseCurrency($value): float
    {
        return (float) preg_replace('/[^0-9]/', '', $value);
    }

    private function generateBayarZakatId(int $userId): string
    {
        $date = now()->format('Ymd');
        $count = BayarZakat::whereDate('created_at', now())->where('id_pemilik', $userId)->count();
        return sprintf("ZKT-%d%s%02d", $userId, $date, $count + 1);
    }

    private function notifySuccess(): void
    {
        Notification::make()
            ->title('Berhasil')
            ->body('Pembayaran zakat telah disimpan')
            ->success()
            ->send();
    }

    private function notifyError(string $message): void
    {
        Notification::make()
            ->title('Terjadi Kesalahan')
            ->body($message)
            ->danger()
            ->send();
    }

    private function redirectToIndex(): void
    {
        $this->redirect(PembayaranZakatResource::getUrl('index'), navigate: true);
    }
}
