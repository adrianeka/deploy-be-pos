<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatZakatResource\Pages;
use App\Filament\Resources\RiwayatZakatResource\RelationManagers\PenjualanRelationManager;
use App\Models\BayarZakat;
use Filament\Forms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class RiwayatZakatResource extends Resource
{
    protected static ?string $model = BayarZakat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'Riwayat Zakat';
    protected static ?string $pluralLabel = 'Riwayat Zakat';
    protected static ?string $recordTitleAttribute = 'id_penerima_zakat';
    protected static ?string $navigationLabel = 'Riwayat Zakat';
    protected static ?string $navigationGroup = 'Zakat';
    protected static ?int $navigationSort = 6;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }
    
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['penjualan']); // Eager load relationship
    }

    public static function canCreate(): bool
    {
        return false; // Ini akan menghilangkan tombol create
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('namaPenerima')->label('Nama Penerima')->searchable()->sortable(),
                TextColumn::make('nominal_zakat')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                TextColumn::make('tanggal_bayar')->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                // 
            ])
            ->defaultSort('tanggal_bayar', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Transaksi Penjualan')
                    ->schema([
                        Grid::make(2) // 2 kolom per baris
                            ->schema([
                                TextEntry::make('modal_terjual')
                                    ->label('Modal yang Terjual')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('nominal_zakat')
                                    ->label('Total Bayar Zakat (2.5%)')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('tanggal_bayar')
                                    ->label('Tanggal Bayar')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),

                                TextEntry::make('metode_pembayaran.jenis_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                                TextEntry::make('metode_pembayaran.tipe_transfer.metode_transfer')
                                    ->label('Metode Transfer')
                                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-'),
                                
                                TextEntry::make('metode_pembayaran.tipe_transfer.jenis_transfer')
                                    ->label('Jenis Transfer')
                                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-')
                            ]),
                    ])
                    ->collapsible(),
                Section::make('Data Penerima Zakat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('penerima_zakat.nama_penerima')
                                    ->label('Nama')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                                TextEntry::make('penerima_zakat.nama_bank')
                                    ->label('Nama Bank')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                                TextEntry::make('penerima_zakat.no_telp')
                                    ->label('Nomor Telepon'),
                                TextEntry::make('penerima_zakat.alamat')
                                    ->label('Alamat Penerima Zakat')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                                TextEntry::make('penerima_zakat.no_rekening')
                                    ->label('Nomor Rekening'),
                                TextEntry::make('penerima_zakat.created_at')
                                    ->label('Dibuat Pada')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
                                TextEntry::make('penerima_zakat.rekening_atas_nama')
                                    ->label("Nama Pemilik Rekening")
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),
                            ])
                    ])
                    ->collapsible()
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PenjualanRelationManager::class
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRiwayatZakats::route('/'),
            'view' => Pages\ViewRiwayatZakat::route('/{record}'),
        ];
    }
}
