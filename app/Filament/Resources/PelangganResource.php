<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PelangganResource\Pages;
use App\Models\Pelanggan;
use Filament\Tables\Actions\CreateAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PelangganResource extends Resource
{
    // protected static ?string $model = Pelanggan::class;

    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $model = Pelanggan::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Pelanggan';
    protected static ?string $pluralLabel = 'Pelanggan';
    protected static ?string $navigationLabel = 'Pelanggan';
    protected static ?string $navigationGroup = 'Data Master';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            TextInput::make('nama_pelanggan')
                ->label('Nama Pelanggan')
                ->regex('/^[A-Za-z\s]+$/')
                ->required()
                ->maxLength(255),

            TextInput::make('no_telp')
                ->label('Nomor Telepon')
                ->numeric()
                ->minLength(10)
                ->maxLength(13)
                ->required(),

            TextInput::make('alamat')
                ->label('Alamat')
                ->required()
                ->maxLength(1000),

            Hidden::make('id_pemilik') // Sembunyikan field
                ->default(fn() => Filament::auth()->id())
                ->dehydrated(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Pelanggan::query()->where('id_pemilik', Filament::auth()->user()->id)) // Filter berdasarkan user login
            ->columns([
                TextColumn::make('nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_telp')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('alamat')
                    ->label('Alamat')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
            ])
            ->filters([
                // Filters dapat ditambahkan sesuai kebutuhan
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])->bulkActions([
                BulkAction::make('delete_selected')
                    ->label('Hapus yang Dipilih')
                    ->action(fn($records) => $records->each->delete())
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ])->headerActions([
                CreateAction::make()
                    ->label('Tambah Pelanggan')
                    ->modalHeading('Tambah Pelanggan Baru')
                    ->modalDescription('Silakan isi informasi untuk menambahkan pelanggan baru.')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->using(function (array $data): Pelanggan {
                        // Create the product
                        $product = Pelanggan::create($data);

                        return $product;
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPelanggans::route('/'),
        ];
    }
}
