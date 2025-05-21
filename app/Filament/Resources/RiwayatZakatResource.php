<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RiwayatZakatResource\Pages;
use App\Filament\Resources\RiwayatZakatResource\RelationManagers\PenjualanRelationManager;
use App\Models\BayarZakat;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class RiwayatZakatResource extends Resource
{
    protected static ?string $model = BayarZakat::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static ?string $label = 'Riwayat Zakat';
    protected static ?string $pluralLabel = 'Riwayat Zakat';
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
            ->with(['penjualan'])
            ->where('id_pemilik', Auth::user()->id_pemilik); // Filter berdasarkan id_pemilik user login
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('penerimaZakat.nama_penerima')
                    ->label('Nama Penerima')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nominal_zakat')
                    ->label('Total Bayar')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                TextColumn::make('created_at')
                    ->label('Tanggal')
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
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Transaksi Penjualan')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('modal_terjual')
                                    ->label('Modal yang Terjual')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('nominal_zakat')
                                    ->label('Total Bayar Zakat (2.5%)')
                                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                TextEntry::make('created_at')
                                    ->label('Tanggal Bayar')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),

                                TextEntry::make('jenis_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                                TextEntry::make('tipeTransfer.metode_transfer')
                                    ->label('Metode Transfer')
                                    ->visible(fn($record) => $record->jenis_pembayaran === 'transfer')
                                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-'),

                                TextEntry::make('tipeTransfer.jenis_transfer')
                                    ->label('Jenis Transfer')
                                    ->visible(fn($record) => $record->jenis_pembayaran === 'transfer')
                                    ->formatStateUsing(fn($state) => $state ? Str::ucfirst($state) : '-')
                            ]),
                    ])
                    ->collapsible(),
                Section::make('Data Penerima Zakat')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('penerimaZakat.nama_penerima')
                                    ->label('Nama')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                                TextEntry::make('penerimaZakat.nama_bank')
                                    ->label('Nama Bank')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                                TextEntry::make('penerimaZakat.no_telp')
                                    ->label('Nomor Telepon'),

                                TextEntry::make('penerimaZakat.alamat')
                                    ->label('Alamat Penerima Zakat')
                                    ->formatStateUsing(fn($state) => Str::ucfirst($state)),

                                TextEntry::make('penerimaZakat.no_rekening')
                                    ->label('Nomor Rekening'),

                                TextEntry::make('penerimaZakat.created_at')
                                    ->label('Dibuat Pada')
                                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),

                                TextEntry::make('penerimaZakat.rekening_atas_nama')
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
