<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatPenjualanResource\Pages;
use App\Models\Penjualan;
use Filament\Facades\Filament;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use App\Filament\Resources\RiwayatPenjualanResource\RelationManagers;

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
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Penjualan::query()
                    ->join('pelanggan', 'penjualan.id_pelanggan', '=', 'pelanggan.id_pelanggan')
                    ->where('pelanggan.id_pemilik', Filament::auth()->id());
            })
            ->defaultSort('tanggal_penjualan', 'desc')
            ->columns([
                TextColumn::make('id_penjualan')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('pelanggan.nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('kasir.nama')
                    ->label('Kasir yang Melayani')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal_penjualan')
                    ->label('Tanggal Penjualan')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Filter::make('tanggal_penjualan')
                    ->form([
                        DatePicker::make('from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari: ' . $data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'Sampai: ' . $data['until'];
                        }

                        return $indicators;
                    })
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
                DeleteBulkAction::make()
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

                                        TextEntry::make('uang_diterima') // Note the snake_case format for the accessor
                                            ->label('Uang Diterima')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),

                                        TextEntry::make('uang_kembalian') // Note the snake_case format for the accessor
                                            ->label('Uang Kembalian')
                                            ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('status_penjualan')
                                            ->label('Status')
                                            ->formatStateUsing(function ($state) {
                                                $status = $state; // Menangkap status dari record

                                                // Mengganti status dengan label yang lebih deskriptif
                                                switch ($status) {
                                                    case 'lunas':
                                                        return 'Lunas';
                                                    case 'belum lunas':
                                                        return 'Belum Lunas';
                                                    case 'pesanan':
                                                        return 'Pesanan';
                                                }
                                            }),
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

    public static function getRelations(): array
    {
        return [
            RelationManagers\ProdukRelationManager::class,
            RelationManagers\PembayaranRelationManager::class,
        ];
    }

    public static function getLabel(): string
    {
        return 'Data Pembayaran'; // Ini fallback label untuk item tunggal
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
}
