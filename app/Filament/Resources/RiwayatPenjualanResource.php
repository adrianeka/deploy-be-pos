<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPenjualanResource\Pages;
use App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\MetodePembayaran;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components;
use App\Models\Kasir;
use App\Models\Pelanggan;
use Illuminate\Support\Str;

class RiwayatPenjualanResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'Riwayat Transaksi Penjualan';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Riwayat Transaksi Penjualan';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Data Penjualan')
                    ->schema([
                        Components\Grid::make(3)
                            ->schema([
                                Components\TextInput::make('id_penjualan')
                                    ->label('Nomor Invoice')
                                    ->disabled(),

                                Components\Select::make('id_kasir')
                                    ->label('Kasir')
                                    ->options(Kasir::all()->pluck('nama', 'id_kasir'))
                                    ->searchable()
                                    ->required(),

                                Components\Select::make('id_pelanggan')
                                    ->label('Pelanggan')
                                    ->options(Pelanggan::all()->pluck('nama_pelanggan', 'id_pelanggan'))
                                    ->searchable()
                                    ->required(),

                                Components\TextInput::make('diskon')
                                    ->label('Diskon')
                                    ->numeric()
                                    ->default(0),

                                Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->disabled(),

                                Components\Select::make('status_penjualan')
                                    ->label('Status')
                                    ->options([
                                        'lunas' => 'Lunas',
                                        'belum lunas' => 'Belum Lunas',
                                        'pesanan' => 'Pesanan',
                                    ])
                                    ->required(),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Penjualan::with(['pelanggan', 'kasir'])
                ->join('pelanggan', 'penjualan.id_pelanggan', '=', 'pelanggan.id_pelanggan')
                ->where('pelanggan.id_pemilik', Filament::auth()->id()))
            ->defaultSort('tanggal_penjualan', 'desc')
            ->columns([
                TextColumn::make('id_penjualan')->label('Nomor Invoice')->searchable()->sortable(),
                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->formatStateUsing(fn($state) => Str::ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->formatStateUsing(fn($state) => Str::ucfirst($state))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_harga')->label('Total Harga')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.'))
                    ->sortable(),
                TextColumn::make('tanggal_penjualan')->label('Tanggal Penjualan')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('tanggal_penjualan')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('tanggal_penjualan', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('tanggal_penjualan', '<=', $data['until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Transaksi Penjualan')
                    ->schema([
                        Split::make([
                            Grid::make(3)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('id_penjualan')->label('Nomor Invoice'),
                                        TextEntry::make('kasir.nama')
                                            ->label('Nama Kasir yang Melayani')
                                            ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                                        TextEntry::make('pelanggan.nama_pelanggan')
                                            ->label('Nama Pelanggan')
                                            ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                                    ]),
                                    Group::make([
                                        TextEntry::make('total_harga')->label('Total Harga Transaksi')
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                                        TextEntry::make('uang_diterima')->label('Uang yang telah Diterima')
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                                        TextEntry::make('sisa_pembayaran')
                                            ->label(fn($record) => in_array($record->status_penjualan, ['belum lunas', 'pesanan']) ? 'Sisa Pembayaran' : 'Uang Kembalian')
                                            ->state(
                                                fn($record) => in_array($record->status_penjualan, ['belum lunas', 'pesanan'])
                                                    ? $record->sisa_pembayaran
                                                    : $record->uang_kembalian
                                            )
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                                    ]),
                                    Group::make([
                                        TextEntry::make('status_penjualan')->label('Status Transaksi')
                                            ->formatStateUsing(function ($state) {
                                                return match ($state) {
                                                    'lunas' => 'Lunas',
                                                    'belum lunas' => 'Belum Lunas',
                                                    'pesanan' => 'Pesanan',
                                                    default => Str::ucfirst($state),
                                                };
                                            }),
                                        TextEntry::make('diskon')->label('Diskon yang Diberikan')
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                                        TextEntry::make('created_at')->label('Tanggal Transaksi')
                                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //RelationManagers\PenjualanDetailRelationManager::class,
            RelationManagers\PembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayatPenjualans::route('/'),
            'edit' => Pages\EditRiwayatPenjualan::route('/{record}/edit'),
            'view' => Pages\ViewRiwayatPenjualan::route('/{record}'),
        ];
    }
}
