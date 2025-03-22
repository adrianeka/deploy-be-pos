<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StokResource\Pages;
use App\Models\Stok;
use App\Models\Produk;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Split;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Resources\Pages\Page;
use Filament\Forms\Components;

class StokResource extends Resource
{
    protected static ?string $model = Stok::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $label = 'Stok Produk';
    protected static ?string $pluralLabel = 'Stok Produk';
    protected static ?string $navigationLabel = 'Stok Produk';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 0;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Stok')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Select::make('id_produk')
                                    ->label('Produk')
                                    ->options(fn() => Produk::where('id_pemilik', Filament::auth()->id())->pluck('nama_produk', 'id_produk'))
                                    ->searchable()
                                    ->required(),
                                Select::make('jenis_stok')
                                    ->label('Jenis Stok')
                                    ->options([
                                        'In' => 'Masuk (In)',
                                        'Out' => 'Keluar (Out)',
                                    ])
                                    ->required(),
                                TextInput::make('jumlah_stok')
                                    ->label('Jumlah Stok')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1),
                                TextInput::make('keterangan')
                                    ->label('Keterangan')
                                    ->required()
                                    ->maxLength(255),
                            ]),
                    ])
                    ->collapsible(),

                Hidden::make('jenis_transaksi')->default('Manual'),
                Hidden::make('tanggal_stok')->default(fn() => now()),
                Hidden::make('id_pemilik')->default(fn() => Filament::auth()->id()),
            ]);
    }


    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                $produk = new Produk();
                return $produk->getStokProdukByPemilik(Filament::auth()->id());
            })
            ->columns([
                TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stok_tersedia')
                    ->label('Stok Tersedia')
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        return Stok::getStokTersediaByProduk($record->id_produk);
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Produk')
                    ->schema([
                        Split::make([
                            Grid::make(3)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('produk.nama_produk')
                                            ->label('Nama Produk'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('stok_tersedia')
                                            ->label('Total Stok Tersedia')
                                            ->getStateUsing(function ($record) {
                                                return Stok::getStokTersediaByProduk($record->id_produk);
                                            }),
                                    ]),
                                    Group::make([
                                        TextEntry::make('produk.satuan.nama_satuan')
                                            ->label('Satuan')
                                    ]),
                                ]),
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            Pages\ViewStok::class,
            Pages\RiwayatStok::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListStoks::route('/'),
            'create' => Pages\CreateStok::route('/create'),
            'comments' => Pages\RiwayatStok::route('/{record}/riwayat-stok'),
            'view' => Pages\ViewStok::route('/{record}'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['nama_produk']);
    }
}
