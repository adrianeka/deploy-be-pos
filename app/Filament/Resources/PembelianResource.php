<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Filament\Resources\PembelianResource\RelationManagers\PembelianDetailRelationManager;
use App\Models\Pemasok;
use App\Models\Pembelian;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Filament\Infolists\Components\Section; // ✅ Tambahkan ini
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Infolist;

class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $label = 'Transaksi Pembelian';
    protected static ?string $recordTitleAttribute = 'pemasok.nama_perusahaan';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Transaksi Pembelian';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Pemasok')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('produk.nama_perusahaan')
                                    ->label('Nama Perusahaan')
                                    ->maxLength(255)
                                    ->disabled(),
                                Components\TextInput::make('tanggal_pembelian')
                                    ->label('Tanggal Pembelian')
                                    ->disabled(),
                                Components\TextInput::make('status_pembelian')
                                    ->label('Status Pembelian')
                                    ->disabled(),
                            ])
                    ])
                    ->collapsible(),
                Components\Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()->id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Pembelian::query()->where('id_pemilik', Filament::auth()->user()->id))
            ->columns([
                TextColumn::make('pemasok.nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->getStateUsing(function (Pembelian $record): string {
                        return 'Rp. ' . number_format($record->total_harga, 0, ',', '.');
                    }),
                TextColumn::make('created_at')
                    ->label('Tanggal Pembelian')
                    ->searchable()
                    ->sortable()
                    ->dateTime(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            PembelianDetailRelationManager::class, // Tambahkan ini
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Transaksi Pembelian')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('pemasok.nama_perusahaan')
                                            ->label('Nama Perusahaan'),
                                        TextEntry::make('tanggal_pembelian')
                                            ->label('Tanggal Pembelian')
                                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
                                    ]),
                                    Group::make([
                                        TextEntry::make('status_pembelian')
                                            ->label('Status Pembelian'),
                                        TextEntry::make('total_harga')
                                            ->label('Total Harga')
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                                    ]),
                                ]),
                        ]),
                    ]),
        ]);
}


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            // 'create' => Pages\CreatePembelian::route('/create'),
            // 'edit' => Pages\EditPembelian::route('/{record}/edit'),
            'view' => Pages\ViewPembelian::route('/{record}'),
        ];
    }
}
