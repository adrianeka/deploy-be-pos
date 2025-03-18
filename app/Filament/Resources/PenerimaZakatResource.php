<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerimaZakatResource\Pages;
use App\Models\PenerimaZakat;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\BulkAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;

class PenerimaZakatResource extends Resource
{
    protected static ?string $model = PenerimaZakat::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Penerima Zakat';
    protected static ?string $pluralLabel = 'Penerima Zakat';
    protected static ?string $navigationLabel = 'Penerima Zakat';
    protected static ?string $navigationGroup = 'Data Master';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('nama_penerima')
                    ->label('Nama Penerima')
                    ->regex('/^[A-Za-z\s]+$/')
                    ->searchable()
                    ->required()
                    ->maxLength(255),

                TextInput::make('no_telp')
                    ->label('Nomor Telepon')
                    ->numeric()
                    ->minLength(10)
                    ->maxLength(13)
                    ->searchable()
                    ->required(),

                TextInput::make('no_rekening')
                    ->label('Nomor Rekening')
                    ->numeric()
                    ->length(16)
                    ->searchable()
                    ->required(),

                TextInput::make('nama_bank')
                    ->label('Nama Bank')
                    ->regex('/^[A-Za-z\s]+$/')
                    ->required()
                    ->searchable()
                    ->maxLength(255),

                TextInput::make('rekening_atas_nama')
                    ->label('Nama Pemilik Rekening')
                    ->regex('/^[A-Za-z\s]+$/')
                    ->required()
                    ->searchable()
                    ->maxLength(255),

                TextInput::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->searchable()
                    ->maxLength(1000),

                Hidden::make('id_pemilik') // Sembunyikan field
                    ->default(fn() => Filament::auth()->id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => PenerimaZakat::query()->where('id_pemilik', Filament::auth()->user()->id)) // Filter berdasarkan user login
            ->columns([
                TextColumn::make('nama_penerima')
                    ->label('Nama Penerima')
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
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('nama_penerima')
                                            ->label('Nama Penerima Zakat'),
                                        TextEntry::make('no_telp')
                                            ->label('Nomor Handphone'),
                                        TextEntry::make('no_rekening')
                                            ->label('Nomor Rekening'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('nama_bank')
                                            ->label('Nama Bank'),
                                        TextEntry::make('rekening_atas_nama')
                                            ->label('Nama Pemilik Rekening'),
                                        TextEntry::make('alamat')
                                            ->label('Alamat Penerima Zakat'),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations dapat ditambahkan sesuai kebutuhan
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerimaZakats::route('/'),
        ];
    }
}
