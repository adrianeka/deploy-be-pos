<?php

namespace App\Filament\Resources\StokResource\Pages;

use App\Filament\Resources\StokResource;
use App\Models\Stok;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RiwayatStok extends ManageRelatedRecords
{
    protected static string $resource = StokResource::class;
    protected static string $relationship = 'stok';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Stok';
    protected static ?string $title = 'Riwayat Stok Produk';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Stok';
    }

    public function table(Table $table): Table
    {
        $id_produk = $this->record->id_produk;

        return $table
            ->query(
                Stok::query()
                    ->where('id_produk', $id_produk)
                    ->where('jenis_transaksi', '!=', 'Stok Awal') // Menghilangkan data dengan keterangan "Stok Awal"
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('jenis_stok')
                    ->label('Jenis Stok')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->color(fn(string $state): string => match ($state) {
                        'In' => 'success',
                        'Out' => 'danger',
                    }),
                TextColumn::make('jumlah_stok')
                    ->label('Jumlah')
                    ->numeric()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('jenis_transaksi')
                    ->label('Jenis Transaksi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('jenis_stok')
                    ->label('Filter Jenis Stok')
                    ->options([
                        'In' => 'Masuk (In)',
                        'Out' => 'Keluar (Out)',
                    ]),
            ])
            ->actions([])
            ->bulkActions([])
            ->headerActions([]);
    }
}
