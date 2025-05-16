<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPenjualanResource\Pages;
use App\Models\Penjualan;
use App\Enums\StatusTransaksiPenjualan;
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
use Filament\Forms\Components\Repeater;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class RiwayatPenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'Riwayat Transaksi Penjualan';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Riwayat Penjualan';
    protected static ?int $navigationSort = 2;
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

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn(Penjualan $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
                            ->content(fn(Penjualan $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Penjualan $record) => $record === null),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Penjualan::with(['pelanggan', 'kasir'])
                    ->whereHas('pelanggan', fn($query) => $query->where('id_pemilik', Filament::auth()->id()));
            })
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
                ->disabled()
                ->required(),
            //->unique(Penjualan::class, 'number', ignoreRecord: true),

            Forms\Components\Select::make('id_pelanggan')
                ->relationship('pelanggan', 'nama_pelanggan')
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

            Forms\Components\ToggleButtons::make('status_penjualan')
                ->label('Status Penjualan')
                ->inline(true)
                ->options(StatusTransaksiPenjualan::class)
                ->required(),
        ];
    }

    public static function getProdukRepeater(): Repeater
    {
        return Repeater::make('penjualanDetail')
            ->relationship('penjualanDetail')
            ->schema([
                // Row 1
                Forms\Components\Select::make('id_produk')
                    ->label('Produk')
                    ->relationship('produk', 'nama_produk')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                        if ($state) {
                            $standardLevel = LevelHarga::where('id_produk', $state)
                                ->whereIn('nama_level', ['Standard', 'Standart', 'Default'])
                                ->first();

                            if ($standardLevel) {
                                $set('id_level_harga', $standardLevel->id_level_harga);
                                $set('harga_jual', $standardLevel->harga_jual);

                                $jumlah = $get('jumlah_produk') ?: 1;
                                $set('total_harga', $standardLevel->harga_jual * $jumlah);
                            } else {
                                $set('id_level_harga', null);
                                $set('harga_jual', 0);
                                $set('total_harga', 0);
                            }
                        } else {
                            $set('id_level_harga', null);
                            $set('harga_jual', 0);
                            $set('total_harga', 0);
                        }

                        if (!$get('jumlah_produk')) {
                            $set('jumlah_produk', 1);
                        }
                    })

                    ->distinct()
                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                    ->columnSpan([
                        'md' => 5,
                    ])
                    ->searchable(),

                Forms\Components\Select::make('id_level_harga')
                    ->label('Level Harga')
                    ->options(fn() => LevelHarga::pluck('nama_level', 'id_level_harga')->toArray())
                    ->default(function (?PenjualanDetail $record) {
                        if ($record) {
                            $hargaJual = $record->harga_jual;
                            $productId = $record->id_produk;

                            $levelHarga = LevelHarga::where('id_produk', $productId)
                                ->where('harga_jual', $hargaJual)
                                ->first();

                            return $levelHarga?->id_level_harga;
                        }

                        return null;
                    })

                    ->disabled(function (callable $get) {
                        // Disable select level harga jika produk belum dipilih
                        return !$get('id_produk');
                    })
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                        // Jika level harga dipilih
                        if ($state) {
                            // Ambil level harga yang dipilih
                            $levelHarga = LevelHarga::find($state);
                            if ($levelHarga) {
                                // Set harga jual berdasarkan level harga
                                $set('harga_jual', $levelHarga->harga_jual);

                                // Hitung total harga berdasarkan jumlah produk
                                $jumlah = $get('jumlah_produk') ?: 1;
                                $set('total_harga', $levelHarga->harga_jual * $jumlah);
                            }
                        } else {
                            // Reset harga jual dan total harga jika level harga dihapus
                            $set('harga_jual', 0);
                            $set('total_harga', 0);
                        }
                    })
                    ->columnSpan([
                        'md' => 5,
                    ]),

                // Row 2
                Forms\Components\TextInput::make('jumlah_produk')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(1)
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                        $harga = $get('harga_jual') ?: 0;
                        $jumlah = $state ?: 1;
                        $set('total_harga', $harga * $jumlah);
                    })
                    ->columnSpan([
                        'md' => 3,
                    ]),

                Forms\Components\TextInput::make('harga_jual')
                    ->label('Harga Jual')
                    ->numeric()
                    ->required()
                    ->disabled()
                    ->reactive()
                    ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                        $jumlah = $get('jumlah_produk') ?: 1;
                        $set('total_harga', $state * $jumlah);
                    })
                    ->columnSpan([
                        'md' => 3,
                    ]),

                Forms\Components\TextInput::make('total_harga')
                    ->label('Total Harga')
                    ->numeric()
                    ->disabled()
                    ->dehydrated(false) // Don't save this to database
                    ->formatStateUsing(function ($state, callable $get) {
                        if (!$state) {
                            $harga = $get('harga_jual') ?: 0;
                            $jumlah = $get('jumlah_produk') ?: 1;
                            return $harga * $jumlah;
                        }
                        return $state;
                    })
                    ->columnSpan([
                        'md' => 4,
                    ]),
            ])
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
    }

    public static function getPembayaranRepeater(): Repeater
    {
        return Repeater::make('pembayaranData')
            ->schema([
                Forms\Components\Select::make('id_metode_pembayaran')
                    ->label('Metode Pembayaran')
                    ->options(function () {
                        return \App\Models\MetodePembayaran::pluck('nama_metode', 'id_metode_pembayaran');
                    })
                    ->searchable()
                    ->required()
                    ->columnSpan([
                        'md' => 5,
                    ]),

                Forms\Components\TextInput::make('total_bayar')
                    ->label('Total Bayar')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->columnSpan([
                        'md' => 5,
                    ]),

                Forms\Components\DateTimePicker::make('tanggal_pembayaran')
                    ->label('Tanggal Pembayaran')
                    ->default(now())
                    ->required()
                    ->columnSpan([
                        'md' => 5,
                    ]),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->maxLength(255)
                    ->columnSpan([
                        'md' => 5,
                    ]),
            ])
            ->default(function ($record) {
                if (!$record) return [];

                // Try to get payment data through the relationship chain
                $paymentData = [];

                if ($record->pembayaranPenjualan && $record->pembayaranPenjualan->pembayaran) {
                    $pembayaran = $record->pembayaranPenjualan->pembayaran;
                    $paymentData[] = [
                        'id_metode_pembayaran' => $pembayaran->id_metode_pembayaran,
                        'total_bayar' => $pembayaran->total_bayar,
                        'tanggal_pembayaran' => $pembayaran->tanggal_pembayaran,
                        'keterangan' => $pembayaran->keterangan,
                    ];
                }

                return $paymentData;
            })
            ->defaultItems(0) // Don't add default items, we'll add them from the database
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ]);
    }
}
