<?php

namespace App\Filament\Resources;

use App\Filament\Exports\PembelianExporter;
use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\Widgets\PembelianOverview;
use App\Models\Pembelian;
use App\Models\TipeTransfer;
use Illuminate\Database\Eloquent\Model;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Tuxones\JsMoneyField\Tables\Columns\JSMoneyColumn;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $label = 'Transaksi Pembelian';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Pembelian Produk';
    protected static ?string $slug = 'pembelian';
    protected static ?int $navigationSort = 1;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static array $metodePembayaranOptions = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer',
    ];

    protected static array $tipePembayaranOptions = [
        'bank' => 'Bank',
        'e-wallet' => 'E-wallet',
    ];

    public static function getWidgets(): array
    {
        return [
            PembelianOverview::class,
        ];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Group::make()
                    ->schema([
                        Components\Section::make('Form Pembelian')
                            ->schema(static::getDetailsFormSchema())
                            ->collapsible()
                            ->columns(2),

                        Components\Section::make('Data Produk')
                            ->collapsible()
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Apakah Anda yakin?')
                                    ->modalDescription('Semua produk yang sudah ada akan dihapus')
                                    ->requiresConfirmation()
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn(Forms\Set $set) => $set('produk', [])),
                            ])
                            ->schema([
                                static::getProdukRepeater(),
                            ]),

                        Components\Section::make('Data Pembayaran')
                            ->collapsible()
                            ->headerActions([
                                Action::make('reset')
                                    ->modalHeading('Apakah Anda yakin?')
                                    ->modalDescription('Semua pembayaran yang sudah ada akan dihapus')
                                    ->requiresConfirmation()
                                    ->requiresConfirmation()
                                    ->color('danger')
                                    ->action(fn(Forms\Set $set) => $set('pembayaran', [])),
                            ])
                            ->schema([
                                static::getPembayaranRepeater(),
                            ]),
                    ])
                    ->columnSpan(['lg' => fn(?Pembelian $record) => $record === null ? 3 : 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Pembelian::query()
                    ->whereHas('pemasok', function ($query) {
                        $query->where('id_pemilik', Filament::auth()->id());
                    });
            })
            ->headerActions([
                ExportAction::make()
                    ->exporter(PembelianExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ])
                    ->fileName(function (Export $export): string {
                        $date = now()->format('Ymd');
                        return "Laporan Pembelian-{$date}.csv";
                    })
            ])
            ->defaultSort('pembelian.created_at', 'desc')
            ->columns([
                TextColumn::make('id_pembelian')
                    ->label('Nomor Invoice')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('pemasok.nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                JSMoneyColumn::make('total_harga')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                    ]),
                TextColumn::make('created_at')
                    ->label('Tanggal Pembelian')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dari tanggal ' . Carbon::parse($data['created_from'])->translatedFormat('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sampai tanggal ' . Carbon::parse($data['created_until'])->translatedFormat('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
            ])
            ->groups([
                Tables\Grouping\Group::make('created_at')
                    ->label('Tanggal Penjualan')
                    ->date()
                    ->collapsible(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3)
                    ->schema([
                        Section::make('Data Transaksi')
                            ->schema([
                                TextEntry::make('pemasok.nama_perusahaan')
                                    ->label('Nama Perusahaan'),

                                TextEntry::make('uang_diterima')
                                    ->label('Total Yang Sudah Dibayar')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('id_pembelian')
                                    ->label('Nomor Invoice'),

                                TextEntry::make('total_harga')
                                    ->label('Total Harga')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state ?? 0, 0, ',', '.')),

                                TextEntry::make('pemasok.alamat')
                                    ->label('Alamat Perusahaan'),

                                TextEntry::make('uang_kembalian')
                                    ->label('Uang Kembalian')
                                    ->visible(fn($record) => $record->uang_kembalian > 0)
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('sisa_bayar')
                                    ->label('Sisa Pembayaran')
                                    ->visible(fn($record) => $record->sisa_bayar > 0)
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                            ])
                            ->columns(2)
                            ->columnSpan(2),

                        Section::make()
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label('Dibuat pada')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->diffForHumans() : '-'),

                                TextEntry::make('updated_at')
                                    ->label('Terakhir diubah pada')
                                    ->formatStateUsing(fn($state) => $state ? \Carbon\Carbon::parse($state)->diffForHumans() : '-'),
                            ])
                            ->hidden(fn(?Pembelian $record) => $record === null)
                            ->columnSpan(1),
                    ]),
            ]);
    }


    public static function getDetailsFormSchema(): array
    {
        return [
            Components\TextInput::make('id_pembelian')
                ->label('Nomor Invoice')
                ->default(function (?Model $record) {
                    if ($record) {
                        return $record->id_pembelian;
                    }

                    $tanggal = Carbon::now()->format('Ymd');
                    $kodeToko = Filament::auth()->user()?->id ?? 0;

                    $latestId = \App\Models\Pembelian::whereDate('created_at', now())
                        ->where('id_pembelian', 'like', "INV-{$kodeToko}{$tanggal}%")
                        ->orderByDesc('id_pembelian')
                        ->value('id_pembelian');

                    $lastNumber = $latestId ? (int)substr($latestId, -3) : 0;
                    $urutan = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);

                    return "INV-{$kodeToko}{$tanggal}{$urutan}";
                })
                ->disabled()
                ->dehydrated(true),

            Components\Select::make('id_pemasok')
                ->label('Nama Perusahaan')
                ->relationship('pemasok', 'nama_perusahaan')
                ->searchable()
                ->preload()
                ->required()
                ->createOptionForm([
                    Components\TextInput::make('nama_perusahaan')
                        ->label('Nama Perusahaan')
                        ->required()
                        ->maxLength(255),

                    Components\TextInput::make('no_telp')
                        ->label('Nomor Telepon')
                        ->required()
                        ->numeric()
                        ->minLength(10)
                        ->maxLength(15),

                    Components\TextInput::make('alamat')
                        ->label('Alamat')
                        ->required()
                        ->maxLength(255),

                    Forms\Components\Hidden::make('id_pemilik')
                        ->default(fn() => Filament::auth()?->id()),

                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading('Tambah Pelanggan')
                        ->modalWidth('lg');
                }),
        ];
    }

    public static function getProdukRepeater(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Repeater::make('pembelianDetail')
                ->relationship('pembelianDetail')

                ->schema([
                    Forms\Components\Select::make('id_produk')
                        ->label('Produk')
                        ->relationship('produk', 'nama_produk')
                        ->required()
                        ->reactive()
                        ->preload()
                        ->distinct()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            $produk = \App\Models\Produk::find($state);
                            if ($produk) {
                                $set('harga_beli', $produk->harga_beli);
                                $set('sub_total_harga', $produk->harga_beli);
                            } else {
                                $set('harga_beli', 0);
                                $set('sub_total_harga', 0);
                            }
                        })
                        ->columnSpan(['md' => 4])
                        ->searchable(),

                    Forms\Components\Grid::make()
                        ->schema([
                            Forms\Components\TextInput::make('jumlah_produk')
                                ->label('Jumlah')
                                ->numeric()
                                ->minValue(1)
                                ->default(1)
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                    $harga = $get('harga_beli') ?: 0;
                                    $jumlah = $state ?: 1;
                                    $set('sub_total_harga', $harga * $jumlah);
                                })
                                ->dehydrated()
                                ->visible(fn(callable $get) => $get('id_produk')),

                            Forms\Components\Placeholder::make('satuan_produk')
                                ->label('Satuan')
                                ->content(function (callable $get) {
                                    $produk = \App\Models\Produk::with('satuan')->find($get('id_produk'));
                                    return $produk?->satuan?->nama_satuan ?? '-';
                                })
                                ->visible(fn(callable $get) => $get('id_produk')),
                        ])
                        ->columns(2)
                        ->columnSpan(['md' => 6]),

                    Forms\Components\Placeholder::make('harga_beli_display')
                        ->label('Harga Beli')
                        ->content(fn(callable $get) => 'Rp. ' . number_format($get('harga_beli') ?? 0, 0, ',', '.'))
                        ->visible(fn(callable $get) => $get('id_produk'))
                        ->columnSpan(['md' => 3]),

                    Forms\Components\Hidden::make('harga_beli')
                        ->dehydrated()
                        ->reactive()
                        ->afterStateHydrated(function (Forms\Set $set, callable $get) {
                            if (!$get('harga_beli')) {
                                $produk = \App\Models\Produk::find($get('id_produk'));
                                $set('harga_beli', $produk?->harga_beli ?? 0);
                            }
                        }),

                    Forms\Components\Placeholder::make('sub_total_harga_display')
                        ->label('Sub Total Harga')
                        ->content(fn(callable $get) => 'Rp. ' . number_format($get('sub_total_harga') ?? 0, 0, ',', '.'))
                        ->visible(fn(callable $get) => $get('id_produk'))
                        ->columnSpan(['md' => 3]),

                    Forms\Components\Hidden::make('sub_total_harga')
                        ->dehydrated()
                        ->reactive()
                        ->afterStateHydrated(function (Forms\Set $set, callable $get) {
                            $harga = $get('harga_beli') ?? 0;
                            $jumlah = $get('jumlah_produk') ?? 1;
                            $set('sub_total_harga', $harga * $jumlah);
                        }),
                ])
                ->dehydrated()
                ->reactive()
                ->addActionLabel('Tambah Produk')
                ->defaultItems(1)
                ->hiddenLabel()
                ->columns([
                    'md' => 10,
                ])
                ->required(),

            Forms\Components\Placeholder::make('total_harga_display')
                ->label('Total Harga')
                ->content(function (callable $get) {
                    $details = $get('pembelianDetail') ?? [];
                    $total = collect($details)->sum(fn($item) => $item['sub_total_harga'] ?? 0);
                    return 'Rp. ' . number_format($total, 0, ',', '.');
                })
                ->columnSpanFull(),
        ])->columnSpanFull();
    }

    public static function getPembayaranFormSchema(): array
    {
        return [
            Components\Select::make('metode_pembayaran')
                ->label('Metode Pembayaran')
                ->options(self::$metodePembayaranOptions)
                ->afterStateUpdated(function ($state, $set) {
                    if ($state === 'tunai') {
                        $set('id_tipe_transfer', null);
                        $set('jenis_transfer', null);
                    }
                })
                ->required()
                ->reactive(),

            Components\TextInput::make('nominal')
                ->label('Nominal')
                ->prefix('Rp. ')
                ->numeric()
                ->required()
                ->visible(fn($get) => in_array($get('metode_pembayaran'), ['tunai', 'transfer'])),

            Components\Select::make('tipe_pembayaran')
                ->label('Tipe Pembayaran')
                ->options(self::$tipePembayaranOptions)
                ->required()
                ->reactive()
                ->visible(fn($get) => $get('metode_pembayaran') === 'transfer'),

            Components\Select::make('id_tipe_transfer')
                ->label('Jenis Transfer')
                ->options(function ($get) {
                    $tipe = $get('tipe_pembayaran');
                    return $tipe ? TipeTransfer::getOpsiByMetodeTransfer($tipe) : [];
                })
                ->required()
                ->visible(
                    fn($get) =>
                    $get('metode_pembayaran') === 'transfer' &&
                        in_array($get('tipe_pembayaran'), ['bank', 'e-wallet'])
                )
                ->searchable()
                ->reactive(),
        ];
    }

    public static function getPembayaranRepeater(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Repeater::make('pembayaranPembelian')
                ->relationship('pembayaranPembelian')
                ->schema([
                    Forms\Components\Hidden::make('id_pembayaran')
                        ->afterStateHydrated(function (Forms\Get $get, Forms\Set $set, ?string $state) {
                            if (!$state) {
                                return;
                            }

                            $pembayaran = \App\Models\Pembayaran::find($state);
                            if ($pembayaran) {
                                $set('total_bayar', $pembayaran->total_bayar);
                                $set('jenis_pembayaran', $pembayaran->jenis_pembayaran);
                                $set('id_tipe_transfer', $pembayaran->id_tipe_transfer);
                                $set('keterangan', $pembayaran->keterangan);

                                if ($pembayaran->id_tipe_transfer) {
                                    $tipeTransfer = \App\Models\TipeTransfer::find($pembayaran->id_tipe_transfer);
                                    if ($tipeTransfer) {
                                        $set('tipe_pembayaran', $tipeTransfer->metode_transfer);
                                    }
                                }
                            }
                        }),

                    Forms\Components\Select::make('jenis_pembayaran')
                        ->label('Metode Pembayaran')
                        ->options(self::$metodePembayaranOptions)
                        ->afterStateUpdated(function ($state, Forms\Set $set) {
                            if ($state === 'tunai') {
                                $set('id_tipe_transfer', null);
                                $set('tipe_pembayaran', null);
                            }
                        })
                        ->required()
                        ->reactive()
                        ->columnSpan(['md' => 3])
                        ->live(),

                    Forms\Components\TextInput::make('total_bayar')
                        ->label('Nominal')
                        ->prefix('Rp. ')
                        ->numeric()
                        ->required()
                        ->columnSpan(['md' => 3])
                        ->visible(fn(callable $get) => in_array($get('jenis_pembayaran'), ['tunai', 'transfer']))
                        ->live()
                        ->afterStateUpdated(fn() => null),

                    Forms\Components\Select::make('tipe_pembayaran')
                        ->label('Tipe Pembayaran')
                        ->options(self::$tipePembayaranOptions)
                        ->required()
                        ->reactive()
                        ->columnSpan(['md' => 2])
                        ->visible(fn(callable $get) => $get('jenis_pembayaran') === 'transfer')
                        ->live(),

                    Forms\Components\Select::make('id_tipe_transfer')
                        ->label('Jenis Transfer')
                        ->options(function (callable $get) {
                            $tipe = $get('tipe_pembayaran');
                            return $tipe ? TipeTransfer::getOpsiByMetodeTransfer($tipe) : [];
                        })
                        ->required()
                        ->visible(
                            fn(callable $get) =>
                            $get('jenis_pembayaran') === 'transfer' &&
                                in_array($get('tipe_pembayaran'), ['bank', 'e-wallet'])
                        )
                        ->searchable()
                        ->reactive()
                        ->columnSpan(['md' => 2]),
                ])
                ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                    $pembayaran = \App\Models\Pembayaran::create([
                        'total_bayar' => $data['total_bayar'],
                        'jenis_pembayaran' => $data['jenis_pembayaran'],
                        'id_tipe_transfer' => $data['id_tipe_transfer'] ?? null,
                        'keterangan' => $data['keterangan'] ?? null,
                    ]);

                    return [
                        'id_pembayaran' => $pembayaran->id_pembayaran,
                    ];
                })
                ->dehydrated()
                ->reactive()
                ->addActionLabel('Tambah Pembayaran')
                ->defaultItems(1)
                ->hiddenLabel()
                ->columns([
                    'md' => 10,
                ])
                ->live(true),

            Forms\Components\Placeholder::make('total_pembayaran_display')
                ->label('Total Pembayaran')
                ->content(function (Forms\Get $get, ?Pembelian $record) {
                    if ($record) {
                        return 'Rp. ' . number_format($record->uang_diterima, 0, ',', '.');
                    }

                    $pembayaranItems = $get('pembayaranPembelian') ?? [];
                    $total = 0;

                    foreach ($pembayaranItems as $item) {
                        $total += $item['total_bayar'] ?? 0;
                    }

                    return 'Rp. ' . number_format($total, 0, ',', '.');
                })
                ->columnSpanFull(),

            Forms\Components\Placeholder::make('status_pembayaran')
                ->label(function (Forms\Get $get) {
                    $pembayaranItems = $get('pembayaranPembelian') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $pembelianDetail = $get('pembelianDetail') ?? [];
                    $totalPembelian = 0;

                    foreach ($pembelianDetail as $item) {
                        $totalPembelian += $item['sub_total_harga'] ?? 0;
                    }

                    return $totalPembayaran > $totalPembelian ? 'Uang Kembalian' : 'Sisa Pembayaran';
                })
                ->content(function (Forms\Get $get, ?Pembelian $record) {
                    $pembayaranItems = $get('pembayaranPembelian') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $pembelianDetail = $get('pembelianDetail') ?? [];
                    $totalPembelian = 0;

                    foreach ($pembelianDetail as $item) {
                        $totalPembelian += $item['sub_total_harga'] ?? 0;
                    }

                    if ($totalPembayaran > $totalPembelian) {
                        $kembalian = $totalPembayaran - $totalPembelian;
                        return 'Rp. ' . number_format($kembalian, 0, ',', '.');
                    } else {
                        $sisa = $totalPembelian - $totalPembayaran;
                        return 'Rp. ' . number_format($sisa, 0, ',', '.');
                    }
                })
                ->columnSpanFull(),

            Forms\Components\Hidden::make('total_harga')
                ->reactive()
                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                    $pembelianDetail = $get('pembelianDetail') ?? [];
                    $totalHarga = 0;

                    foreach ($pembelianDetail as $item) {
                        $totalHarga += $item['sub_total_harga'] ?? 0;
                    }

                    $set('total_harga', $totalHarga);
                })
                ->dehydrated(true),

            Forms\Components\Hidden::make('status_pembelian')
                ->reactive()
                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                    $pembayaranItems = $get('pembayaranPembelian') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $pembelianDetail = $get('pembelianDetail') ?? [];
                    $totalPembelian = 0;

                    foreach ($pembelianDetail as $item) {
                        $totalPembelian += $item['sub_total_harga'] ?? 0;
                    }

                    if ($totalPembayaran >= $totalPembelian) {
                        $set('status_pembelian', 'lunas');
                    } else {
                        $set('status_pembelian', 'belum lunas');
                    }
                })
                ->dehydrated(true),
        ])->columnSpanFull()
            ->reactive()
            ->live();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'edit' => Pages\EditPembelian::route('/{record}/edit'),
            'view' => Pages\ViewTransaksiPembelian::route('/{record}'),
        ];
    }
}
