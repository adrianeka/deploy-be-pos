<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use App\Models\Pembelian;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;

class RiwayatTransaksi extends ManageRelatedRecords
{
    protected static string $resource = PemasokResource::class;
    protected static string $relationship = 'pembelian';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Riwayat Transaksi';
    protected static ?string $title = 'Riwayat Transaksi Pemasok';

    public function getBreadcrumb(): string
    {
        return 'Riwayat Transaksi';
    }

    public function table(Table $table): Table
    {
        $id_pemasok = $this->record->id_pemasok;

        return $table
            ->query(
                Pembelian::query()
                    ->where('id_pemasok', $id_pemasok)
            )
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('id_pembelian')
                    ->label('Nomor Invoice')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_pembelian')
                    ->label('Status Transaksi')
                    ->badge()
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status_pembelian')
                    ->label('Filter Status Transaksi')
                    ->options([
                        'Lunas' => 'Lunas',
                        'Belum Lunas' => 'Belum Lunas',
                        'Pesanan' => 'Pesanan',
                    ]),
            ])
            ->actions([
                Action::make('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => \App\Filament\Resources\PembelianResource::getUrl('view', ['record' => $record]))
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
