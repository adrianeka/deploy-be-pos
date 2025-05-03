<?php

namespace App\Filament\Resources\PemasokResource\Pages;

use App\Filament\Resources\PemasokResource;
use App\Models\Pembelian;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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
            ->defaultSort('tanggal_pembelian', 'desc')
            ->columns([
                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('status_pembelian')
                    ->label('Status Transaksi')
                    ->badge()
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn(string $state): string => ucwords($state))
                    ->color(fn(string $state): string => match ($state) {
                        'Lunas' => 'success',
                        'Belum Lunas' => 'danger',
                        'Pesanan' => 'warning',
                    }),
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
                Action::make('view')
                    ->url(fn() => '#', shouldOpenInNewTab: false)
                    ->icon('heroicon-o-eye'),
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
