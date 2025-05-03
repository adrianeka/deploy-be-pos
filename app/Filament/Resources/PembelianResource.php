<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembelianResource\Pages;
use App\Models\Pembelian;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;


class PembelianResource extends Resource
{
    protected static ?string $model = Pembelian::class;
    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';
    protected static ?string $label = 'Transaksi Pembelian';
    // protected static ?string $recordTitleAttribute = '  ';
    protected static ?string $pluralLabel = 'Transaksi Pembelian';
    protected static ?string $navigationLabel = 'Transaksi Pembelian';
    protected static ?int $navigationSort = 1;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Pembelian')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('id_pemasok')
                                    ->label('Nama Pemasok')
                                    ->required()
                                    ->maxLength(255),
                                Components\TextInput::make('total_harga')
                                    ->label('Total Harga')
                                    ->required()
                                    ->numeric()
                                    ->minLength(10)
                                    ->maxLength(15),
                                Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255),
                            ])
                    ])
                // ->collapsible(),
                // Components\Hidden::make('id_pemilik')
                //     ->default(fn() => Filament::auth()->id())
                //     ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(function () {
                return Pembelian::query()
                    ->join('pemasok', 'pembelian.id_pemasok', '=', 'pemasok.id_pemasok')
                    ->where('pemasok.id_pemilik', Filament::auth()->id());
            })
            ->defaultSort('tanggal_pembelian', 'desc')
            ->columns([
                TextColumn::make('pemasok.nama_perusahaan')
                    ->label('Nama Perusahaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_harga')
                    ->label('Total Harga')
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('tanggal_pembelian')
                    ->label('Tanggal Pembelian')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ]);
    }


    public static function getRelations(): array
    {
        return [
            // RelationManagers\PembelianDetailRelationManager::class,
            // RelationManagers\PembayaranRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembelians::route('/'),
            'create' => Pages\CreatePembelian::route('/create'),
            'view' => Pages\ViewTransaksiPembelian::route('/{record}'),
        ];
    }
}
