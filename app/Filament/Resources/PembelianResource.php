<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use App\Models\TipeTransfer;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $label = 'Transaksi Pembelian';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Pembelian Produk';
    protected static ?int $navigationSort = 1;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static array $metodePembayaranOptions = [
        'tunai' => 'Tunai',
        'transfer' => 'Transfer',
    ];

    protected static array $tipePembayaranOptions = [
        'bank' => 'Bank',
        'e-money' => 'E-Money',
    ];

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
                    ])
                    ->columnSpan(['lg' => fn(?Pembelian $record) => $record === null ? 3 : 2]),

                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Dibuat pada')
                            ->content(fn(Pembelian $record): ?string => $record->created_at?->diffForHumans()),

                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Terakhir diubah pada')
                            ->content(fn(Pembelian $record): ?string => $record->updated_at?->diffForHumans()),
                    ])
                    ->columnSpan(['lg' => 1])
                    ->hidden(fn(?Pembelian $record) => $record === null),
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
            ->defaultSort('pembelian.created_at', 'tanggal_pembelian', 'desc')
            ->columns([
                TextColumn::make('pemasok.nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_harga')
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
                Grid::make(3)
                    ->schema([
                        Section::make('Data Transaksi')
                            ->schema([
                                TextEntry::make('pemasok.nama_perusahaan')
                                    ->label('Nama Perusahaan Pemasok'),

                                TextEntry::make('pemasok.no_telp')
                                    ->label('Nomor Telepon'),

                                TextEntry::make('pemasok.alamat')
                                    ->label('Alamat'),

                                TextEntry::make('total_harga')
                                    ->label('Total Harga')
                                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
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
            Components\Select::make('id_pemasok')
                ->label('Nama Perusahaan Pemasok')
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

    public static function getProdukRepeater(): Repeater
    {
        return Repeater::make('pembelianDetail')
            ->relationship('pembelianDetail')
            ->schema([
                // Row 1
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
                            $set('total_harga', $produk->harga_beli); // default jika jumlah belum diisi
                        } else {
                            $set('harga_beli', 0);
                            $set('total_harga', 0);
                        }
                    })
                    ->columnSpan(['md' => 4])
                    ->searchable(),

                // Row 2
                Forms\Components\Grid::make()
                    ->schema([
                        Forms\Components\TextInput::make('jumlah_produk')
                            ->label('Jumlah')
                            ->numeric()
                            ->default(1)
                            ->required()
                            ->reactive()
                            ->disabled(fn(callable $get) => !$get('id_produk'))
                            ->afterStateUpdated(function ($state, Forms\Set $set, callable $get) {
                                $harga = $get('harga_beli') ?: 0;
                                $jumlah = $state ?: 1;
                                $set('total_harga', $harga * $jumlah);
                            }),

                        Forms\Components\Placeholder::make('satuan_produk')
                            ->label('Satuan')
                            ->content(function (callable $get) {
                                $produk = \App\Models\Produk::with('satuan')->find($get('id_produk'));
                                return $produk?->satuan?->nama_satuan ?? '-';
                            }),
                    ])
                    ->columns(2)
                    ->columnSpan([
                        'md' => 6,
                    ]),

                Forms\Components\Placeholder::make('harga_beli')
                    ->label('Harga Beli')
                    ->content(
                        fn(callable $get) =>
                        'Rp. ' . number_format($get('harga_beli') ?? 0, 0, ',', '.')
                    )
                    ->columnSpan(['md' => 3]),

                Forms\Components\Placeholder::make('total_harga')
                    ->label('Total Harga')
                    ->content(
                        fn(callable $get) =>
                        'Rp. ' . number_format($get('total_harga') ?? 0, 0, ',', '.')
                    )
                    ->columnSpan(['md' => 3]),
            ])
            ->addActionLabel('Tambah Produk')
            ->defaultItems(1)
            ->hiddenLabel()
            ->columns([
                'md' => 10,
            ])
            ->required();
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
                    $tipe = $get('tipe_pembayaran'); // 'bank' atau 'e-money'
                    return $tipe ? TipeTransfer::getOpsiByMetodeTransfer($tipe) : [];
                })
                ->required()
                ->visible(
                    fn($get) =>
                    $get('metode_pembayaran') === 'transfer' &&
                        in_array($get('tipe_pembayaran'), ['bank', 'e-money'])
                )
                ->searchable()
                ->reactive(),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewTransaksiPembelian::route('/{record}'),
        ];
    }
}
