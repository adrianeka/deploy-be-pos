<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPenjualanResource\Pages;
use App\Models\Penjualan;
use App\Filament\Exports\PenjualanExporter;
use App\Filament\Resources\RiwayatPenjualanResource\Widgets\PenjualanOverview;
use App\Models\PenjualanDetail;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Table;
use App\Models\LevelHarga;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use App\Models\TipeTransfer;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Tuxones\JsMoneyField\Forms\Components\JSMoneyInput;

class RiwayatPenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'Transaksi Penjualan';
    protected static ?string $pluralLabel = 'Transaksi Penjualan';
    protected static ?string $slug = 'riwayat-penjualan';
    protected static ?string $navigationLabel = 'Riwayat Penjualan';
    protected static ?int $navigationSort = 2;
    public static function getWidgets(): array
    {
        return [
            PenjualanOverview::class,
        ];
    }

    protected static array $metodePembayaranOptions = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer',
    ];

    protected static array $tipePembayaranOptions = [
        'bank' => 'Bank',
        'e-wallet' => 'E-wallet',
    ];

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Data Penjualan')
                            ->schema(static::getDetailsFormSchema())
                            ->columns(2),

                        Forms\Components\Section::make('Data Produk')
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

                        Forms\Components\Section::make('Data Pembayaran')
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
                    ->columnSpan(['lg' => fn(?Penjualan $record) => $record === null ? 3 : 2]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Penjualan::with(['pelanggan', 'kasir'])
                    ->whereHas('kasir', fn($query) => $query->where('id_pemilik', Filament::auth()->id()));
            })
            ->headerActions([
                ExportAction::make()
                    ->exporter(PenjualanExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ])
                    ->fileName(function (Export $export): string {
                        $date = now()->format('Ymd');
                        return "LaporanPenjualan-{$date}.csv";
                    })
            ])
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->searchable()
                    ->toggleable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                    ]),
                Tables\Columns\TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->toggleable()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Penjualan')
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
                DeleteBulkAction::make()
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
                Section::make('Data Transaksi')
                    ->schema([
                        Split::make([
                            Grid::make(3)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('id_penjualan')
                                            ->label('Nomor Invoice'),
                                        TextEntry::make('kasir.nama')
                                            ->label('Kasir yang Melayani'),
                                        TextEntry::make('pelanggan.nama_pelanggan')
                                            ->label('Nama Pelanggan'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('total_harga')
                                            ->label('Total Harga')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                                        TextEntry::make('uang_diterima')
                                            ->label('Uang Diterima')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                                        TextEntry::make('uang_kembalian')
                                            ->label('Uang Kembalian')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                                    ]),
                                    Group::make([

                                        TextEntry::make('diskon')
                                            ->label('Diskon')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                                        TextEntry::make('created_at')
                                            ->label('Waktu Penjualan')
                                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getLabel(): string
    {
        return 'Data Pembayaran';
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayatPenjualans::route('/'),
            'create' => Pages\CreateRiwayatPenjualan::route('/create'),
            'edit' => Pages\EditRiwayatPenjualan::route('/{record}/edit'),
            'view' => Pages\ViewRiwayatPenjualan::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['id_penjualan', 'kasir', 'pelanggan'];
    }

    public static function getDetailsFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('id_penjualan')
                ->label('Nomor Invoice')
                ->disabled(),

            Forms\Components\Select::make('id_pelanggan')
                ->relationship(
                    'pelanggan',
                    'nama_pelanggan',
                    fn($query) => $query->where('id_pemilik', Filament::auth()->id())
                )
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('nama_pelanggan')
                        ->label('Nama Pelanggan')
                        ->required()
                        ->regex('/^[A-Za-z.\s]+$/')
                        ->debounce(500)
                        ->lazy()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('no_telp')
                        ->label('Nomor Telepon')
                        ->required()
                        ->regex('/^[0-9]+$/')
                        ->minLength(10)
                        ->maxLength(15)
                        ->debounce(500)
                        ->lazy(),

                    Forms\Components\TextInput::make('alamat')
                        ->label('Alamat')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\Hidden::make('id_pemilik')
                        ->default(fn() => Filament::auth()?->id()),

                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading('Tambah Pelanggan')
                        ->modalWidth('lg');
                }),

            Forms\Components\Select::make('id_kasir')
                ->relationship('kasir', 'nama')
                ->searchable()
                ->required()
                ->createOptionForm([
                    Forms\Components\TextInput::make('nama')
                        ->label('Nama')
                        ->required()
                        ->regex('/^[A-Za-z.\s]+$/')
                        ->debounce(500)
                        ->lazy()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('email')
                        ->label('Email')
                        ->required()
                        ->email()
                        ->debounce(500)
                        ->lazy()
                        ->unique(
                            table: User::class,
                            column: 'email',
                            ignoreRecord: true,
                            modifyRuleUsing: fn($rule, $record) =>
                            $record?->user ? $rule->ignore($record->user->id) : $rule
                        )
                        ->formatStateUsing(fn($record) => $record?->user?->email),

                    Forms\Components\TextInput::make('no_telp')
                        ->label('Nomor Telepon')
                        ->required()
                        ->regex('/^[0-9]+$/')
                        ->minLength(10)
                        ->maxLength(13)
                        ->lazy()
                        ->debounce(500),

                    Forms\Components\TextInput::make('alamat')
                        ->label('Alamat')
                        ->required()
                        ->maxLength(255)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('password')
                        ->label('Password')
                        ->password()
                        ->revealable()
                        ->required(fn($context) => $context === 'create')
                        ->dehydrated(fn($state) => filled($state))
                        ->maxLength(255)
                        ->columnSpan(1),

                    Forms\Components\Hidden::make('id_pemilik')
                        ->default(fn() => Filament::auth()?->id()),
                ])
                ->createOptionAction(function (Action $action) {
                    return $action
                        ->modalHeading('Tambah Pelanggan')
                        ->modalWidth('lg');
                }),

            Forms\Components\TextInput::make('diskon')
                ->label('Diskon')
                ->required()
                ->reactive()
                ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                    $total = $get('total_harga') ?: 0;
                    $diskon = $state ?: 0;
                    $set('total_harga', $total - ($total * ($diskon / 100)));
                })
                ->dehydrated(),
        ];
    }

    public static function getProdukRepeater(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Repeater::make('penjualanDetail')
                ->relationship('penjualanDetail')
                ->schema([
                    Forms\Components\Grid::make()
                        ->columns(10)
                        ->schema([
                            Forms\Components\Select::make('id_produk')
                                ->label('Produk')
                                ->relationship('produk', 'nama_produk')
                                ->required()
                                ->reactive()
                                ->preload()
                                ->distinct()
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                    if (!$state) {
                                        $set('id_level_harga', null);
                                        $set('harga_jual', 0);
                                        $set('sub_total_harga', 0);
                                        return;
                                    }
                                    $set('id_level_harga', null);
                                    $set('harga_jual', 0);
                                    $set('sub_total_harga', 0);
                                })
                                ->columnSpan(['md' => 4])
                                ->searchable(),

                            Forms\Components\Select::make('id_level_harga')
                                ->label('Level Harga')
                                ->options(function (callable $get) {
                                    $idProduk = $get('id_produk');
                                    if (!$idProduk) return [];

                                    return \App\Models\LevelHarga::where('id_produk', $idProduk)
                                        ->pluck('nama_level', 'id_level_harga')
                                        ->toArray();
                                })
                                ->default(function (callable $get) {
                                    $hargaJual = $get('harga_jual');
                                    $idProduk = $get('id_produk');

                                    if (!$idProduk || !$hargaJual) return null;

                                    $levelHargaList = \App\Models\LevelHarga::where('id_produk', $idProduk)->get();

                                    $matchingLevel = $levelHargaList->first(function ($item) use ($hargaJual) {
                                        return $item->harga_jual === $hargaJual;
                                    });
                                    return $matchingLevel?->id_level_harga;
                                })
                                ->visible(fn(callable $get) => $get('id_produk'))
                                ->reactive()
                                ->afterStateHydrated(function (Forms\Set $set, callable $get) {
                                    $idLevelHarga = $get('id_level_harga');
                                    if (!$idLevelHarga) {
                                        $hargaJual = $get('harga_jual');
                                        $idProduk = $get('id_produk');

                                        if ($idProduk && $hargaJual) {
                                            $matchingLevel = \App\Models\LevelHarga::where('id_produk', $idProduk)
                                                ->where('harga_jual', $hargaJual)
                                                ->first();

                                            if ($matchingLevel) {
                                                $set('id_level_harga', $matchingLevel->id_level_harga);
                                            }
                                        }
                                    }
                                })
                                ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                    if (!$state) {
                                        $set('harga_jual', 0);
                                        $set('sub_total_harga', 0);
                                        return;
                                    }

                                    $levelHarga = \App\Models\LevelHarga::find($state);
                                    if ($levelHarga) {
                                        $set('harga_jual', $levelHarga->harga_jual);
                                        $jumlah = $get('jumlah_produk') ?? 1;
                                        $set('sub_total_harga', $levelHarga->harga_jual * $jumlah);
                                    }
                                })
                                ->columnSpan(['md' => 2]),

                            Forms\Components\TextInput::make('jumlah_produk')
                                ->label('Jumlah')
                                ->numeric()
                                ->required()
                                ->reactive()
                                ->minValue(1)
                                ->default(1)
                                ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                    $harga = $get('harga_jual') ?: 0;
                                    $jumlah = $state ?: 1;
                                    $set('sub_total_harga', $harga * $jumlah);
                                })
                                ->dehydrated()
                                ->visible(fn(callable $get) => $get('id_level_harga'))
                                ->columnSpan(['md' => 2]),

                            Forms\Components\Placeholder::make('satuan_produk')
                                ->label('Satuan')
                                ->content(function (callable $get) {
                                    $produk = \App\Models\Produk::with('satuan')->find($get('id_produk'));
                                    return $produk?->satuan?->nama_satuan ?? '-';
                                })
                                ->visible(fn(callable $get) => $get('id_level_harga'))
                                ->columnSpan(['md' => 2]),
                        ]),

                    Forms\Components\Placeholder::make('harga_jual_display')
                        ->label('Harga Jual')
                        ->content(fn(callable $get) => 'Rp. ' . number_format($get('harga_jual') ?? 0, 0, ',', '.'))
                        ->visible(fn(callable $get) => $get('id_level_harga'))
                        ->columnSpan(['md' => 2]),

                    Forms\Components\Hidden::make('harga_jual')
                        ->dehydrated()
                        ->reactive()
                        ->afterStateHydrated(function (Forms\Set $set, callable $get, ?PenjualanDetail $record) {
                            if ($record && $record->harga_jual) {
                                $set('harga_jual', $record->harga_jual);
                            } elseif (!$get('harga_jual') && $get('id_level_harga')) {
                                $levelHarga = LevelHarga::find($get('id_level_harga'));
                                $set('harga_jual', $levelHarga?->harga_jual ?? 0);
                            }
                        }),

                    Forms\Components\Placeholder::make('sub_total_harga_display')
                        ->label('Sub Total Harga')
                        ->content(fn(callable $get) => 'Rp. ' . number_format($get('sub_total_harga') ?? 0, 0, ',', '.'))
                        ->visible(fn(callable $get) => $get('id_level_harga'))
                        ->columnSpan(['md' => 2]),

                    Forms\Components\Hidden::make('sub_total_harga')
                        ->dehydrated()
                        ->reactive()
                        ->afterStateHydrated(function (Forms\Set $set, callable $get, ?PenjualanDetail $record) {
                            if ($record && $record->sub_total_harga) {
                                $set('sub_total_harga', $record->sub_total_harga);
                            } else {
                                $harga = $get('harga_jual') ?? 0;
                                $jumlah = $get('jumlah_produk') ?? 1;
                                $set('sub_total_harga', $harga * $jumlah);
                            }
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

            Forms\Components\Placeholder::make('total_penjualan_display')
                ->label('Total Harga')
                ->content(function (callable $get) {
                    $details = $get('penjualanDetail') ?? [];
                    $total = collect($details)->sum(fn($item) => $item['sub_total_harga'] ?? 0);
                    return 'Rp. ' . number_format($total, 0, ',', '.');
                })
                ->columnSpanFull(),
        ])->columnSpanFull();
    }

    public static function getPembayaranRepeater(): Forms\Components\Component
    {
        return Forms\Components\Group::make([
            Forms\Components\Repeater::make('pembayaranPenjualan')
                ->relationship('pembayaranPenjualan')
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

                    JSMoneyInput::make('total_bayar')
                        ->label('Nominal')
                        ->locale('id-ID')
                        ->currency('IDR')
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
                ->content(function (Forms\Get $get, ?Penjualan $record) {
                    if ($record) {
                        $pembayaranItems = $get('pembayaranPenjualan') ?? [];
                        $total = 0;

                        foreach ($pembayaranItems as $item) {
                            $total += $item['total_bayar'] ?? 0;
                        }

                        return 'Rp. ' . number_format($total, 0, ',', '.');
                    }

                    $pembayaranItems = $get('pembayaranPenjualan') ?? [];
                    $total = 0;

                    foreach ($pembayaranItems as $item) {
                        $total += $item['total_bayar'] ?? 0;
                    }

                    return 'Rp. ' . number_format($total, 0, ',', '.');
                })
                ->columnSpanFull()
                ->live(),

            Forms\Components\Placeholder::make('status_pembayaran')
                ->label(function (Forms\Get $get) {
                    $pembayaranItems = $get('pembayaranPenjualan') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $penjualanDetail = $get('penjualanDetail') ?? [];
                    $totalPenjualan = 0;

                    foreach ($penjualanDetail as $item) {
                        $totalPenjualan += $item['sub_total_harga'] ?? 0;
                    }

                    return $totalPembayaran > $totalPenjualan ? 'Uang Kembalian' : 'Sisa Pembayaran';
                })
                ->content(function (Forms\Get $get, ?Penjualan $record) {
                    $pembayaranItems = $get('pembayaranPenjualan') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $penjualanDetail = $get('penjualanDetail') ?? [];
                    $totalPenjualan = 0;

                    foreach ($penjualanDetail as $item) {
                        $totalPenjualan += $item['sub_total_harga'] ?? 0;
                    }

                    if ($totalPembayaran > $totalPenjualan) {
                        $kembalian = $totalPembayaran - $totalPenjualan;
                        return 'Rp. ' . number_format($kembalian, 0, ',', '.');
                    } else {
                        $sisa = $totalPenjualan - $totalPembayaran;
                        return 'Rp. ' . number_format($sisa, 0, ',', '.');
                    }
                })
                ->columnSpanFull()
                ->live(),

            Forms\Components\Hidden::make('total_harga')
                ->reactive()
                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                    $penjualanDetail = $get('penjualanDetail') ?? [];
                    $totalHarga = 0;

                    foreach ($penjualanDetail as $item) {
                        $totalHarga += $item['sub_total_harga'] ?? 0;
                    }

                    $set('total_harga', $totalHarga);
                })
                ->dehydrated(true),

            Forms\Components\Hidden::make('status_penjualan')
                ->reactive()
                ->afterStateHydrated(function (Forms\Set $set, Forms\Get $get) {
                    $pembayaranItems = $get('pembayaranPenjualan') ?? [];
                    $totalPembayaran = 0;

                    foreach ($pembayaranItems as $item) {
                        $totalPembayaran += $item['total_bayar'] ?? 0;
                    }

                    $penjualanDetail = $get('penjualanDetail') ?? [];
                    $totalPenjualan = 0;

                    foreach ($penjualanDetail as $item) {
                        $totalPenjualan += $item['sub_total_harga'] ?? 0;
                    }

                    if ($totalPembayaran >= $totalPenjualan) {
                        $set('status_penjualan', 'lunas');
                    } else {
                        $set('status_penjualan', 'belum lunas');
                    }
                })
                ->dehydrated(true),
        ])->columnSpanFull()
            ->reactive()
            ->live();
    }
}
