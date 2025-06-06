<?php

namespace App\Filament\Resources;

use App\Filament\Exports\StokExporter;
use App\Filament\Resources\StokResource\Pages;
use App\Models\Stok;
use App\Models\Produk;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
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
use Filament\Resources\Pages\Page;
use Filament\Forms\Components;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class StokResource extends Resource
{
    protected static ?string $model = Produk::class;
    protected static ?string $navigationIcon = 'heroicon-o-cube';
    protected static ?string $label = 'Stok Produk';
    protected static ?string $pluralLabel = 'Stok Produk';
    protected static ?string $recordTitleAttribute = 'nama_produk';
    protected static ?string $navigationLabel = 'Stok Produk';
    protected static ?string $slug = 'inventaris/stok-produk';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?int $navigationSort = 5;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik);
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->model(Stok::class)
            ->schema([
                Components\Section::make('Form Stok')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Select::make('id_produk')
                                    ->label('Produk')
                                    ->options(fn() => Produk::where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik)->pluck('nama_produk', 'id_produk'))
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
                                    ->integer()
                                    ->rules(['regex:/^\d+$/'])
                                    ->required()
                                    ->minValue(1),
                            ]),
                    ])
                    ->collapsible(),
                Hidden::make('jenis_transaksi')
                    ->default('Manual'),

                Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()->user()?->pemilik?->id_pemilik),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->headerActions([
                ExportAction::make()
                    ->exporter(StokExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ])
                    ->fileName(function (Export $export): string {
                        $date = now()->format('Ymd');
                        return "Laporan Stok Produk Tersedia-{$date}";
                    })
            ])
            ->columns([
                TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('stok_tersedia')
                    ->label('Stok Tersedia')
                    ->getStateUsing(function ($record) {
                        return Stok::getStokTersediaByProduk($record->id_produk);
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw('(
                            SELECT SUM(CASE WHEN jenis_stok = "In" THEN jumlah_stok ELSE -jumlah_stok END) 
                            FROM stok 
                            WHERE stok.id_produk = produk.id_produk
                        ) ' . $direction);
                    }),
                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_satuan')
                    ->label('Satuan')
                    ->relationship('satuan', 'nama_satuan', function ($query) {
                        return $query->where(function ($query) {
                            $query->where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik)
                                ->orWhereNull('id_pemilik');
                        });
                    })
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('id_kategori')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_kategori', function ($query) {
                        return $query->where(function ($query) {
                            $query->where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik)
                                ->orWhereNull('id_pemilik');
                        });
                    })
                    ->preload()
                    ->multiple(),
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
                                        TextEntry::make('nama_produk')
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
                                        TextEntry::make('satuan.nama_satuan')
                                            ->label('Satuan')
                                    ]),
                                ]),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
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
            'riwayat-stok' => Pages\RiwayatStok::route('/{record}/riwayat-stok'),
            'view' => Pages\ViewStok::route('/{record}'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['nama_produk'];
    }
}
