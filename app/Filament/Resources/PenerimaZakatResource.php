<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PenerimaZakatResource\Pages;
use App\Models\PenerimaZakat;
use Filament\Forms\Components;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Facades\Filament;
use Filament\Pages\SubNavigationPosition;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Page;

class PenerimaZakatResource extends Resource
{
    protected static ?string $model = PenerimaZakat::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $label = 'Penerima Zakat';
    protected static ?string $recordTitleAttribute = 'nama_penerima';
    protected static ?string $pluralLabel = 'Penerima Zakat';
    protected static ?string $navigationLabel = 'Penerima Zakat';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 3;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Penerima Zakat')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('nama_penerima')
                                    ->label('Nama Penerima')
                                    ->regex('/^[A-Za-z.\s]+$/')
                                    ->required()
                                    ->maxLength(255),
                                Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon')
                                    ->numeric()
                                    ->minLength(10)
                                    ->maxLength(13)
                                    ->required(),
                                Components\TextInput::make('no_rekening')
                                    ->label('Nomor Rekening')
                                    ->numeric()
                                    ->minLength(10)
                                    ->maxLength(16)
                                    ->required(),
                                Components\TextInput::make('nama_bank')
                                    ->label('Nama Bank')
                                    ->regex('/^[A-Za-z.\s]+$/')

                                    ->required()
                                    ->maxLength(20),
                                Components\TextInput::make('rekening_atas_nama')
                                    ->label('Nama Pemilik Rekening')
                                    ->regex('/^[A-Za-z.\s]+$/')

                                    ->required()
                                    ->maxLength(30),
                                Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255),
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
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->successNotificationTitle('Data berhasil dihapus'),
            ])->bulkActions([
                DeleteBulkAction::make('delete_selected')
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
                Section::make('Data Penerima Zakat')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('nama_penerima')
                                            ->label('Nama Penerima Zakat'),
                                        TextEntry::make('no_telp')
                                            ->label('Nomor Telepon'),
                                        TextEntry::make('no_rekening')
                                            ->label('Nomor Rekening'),
                                        TextEntry::make('rekening_atas_nama')
                                            ->label('Nama Pemilik Rekening'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('nama_bank')
                                            ->label('Nama Bank'),
                                        TextEntry::make('alamat')
                                            ->label('Alamat Penerima Zakat'),
                                        TextEntry::make('created_at')
                                            ->label('Dibuat pada')
                                            ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Relations dapat ditambahkan sesuai kebutuhan
        ];
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        if ($page instanceof Pages\EditPenerimaZakat) {
            return [];
        }

        return $page->generateNavigationItems([
            Pages\ViewPenerimaZakat::class,
            Pages\RiwayatZakat::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPenerimaZakats::route('/'),
            'create' => Pages\CreatePenerimaZakat::route('/create'),
            'edit' => Pages\EditPenerimaZakat::route('/{record}/edit'),
            'view' => Pages\ViewPenerimaZakat::route('/{record}'),
            'riwayat-zakat' => Pages\RiwayatZakat::route('/{record}/riwayat-zakat'),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
