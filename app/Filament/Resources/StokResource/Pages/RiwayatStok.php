<?php

namespace App\Filament\Resources\StokResource\Pages;

use App\Filament\Exports\StokDetailExporter;
use App\Filament\Resources\StokResource;
use App\Models\Produk;
use App\Models\Stok;
use Filament\Actions\Exports\Enums\ExportFormat;
use Filament\Actions\Exports\Models\Export;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Actions\ExportAction;
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
            ->headerActions([
                ExportAction::make()
                    ->exporter(StokDetailExporter::class)
                    ->formats([
                        ExportFormat::Xlsx,
                    ])
                    ->fileName(function (Export $export): string {
                        $date = now()->format('Ymd');
                        $produk = $this->record->produk; // asumsinya relasi 'produk' sudah ada

                        $namaProduk = ucwords(str($produk->nama_produk ?? 'produk')->slug(' '));
                        // $namaProduk = Produk::find($this->record->$id_produk)->nama_produk;
                        return "Laporan Stok Produk {$namaProduk}-{$date}.csv";
                    })
            ])
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
            ->bulkActions([]);
    }
}
