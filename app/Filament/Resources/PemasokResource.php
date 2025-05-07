<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PemasokResource\Pages;
use App\Models\Pemasok;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Model;

class PemasokResource extends Resource
{
    protected static ?string $model = Pemasok::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?int $navigationSort = 1;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;
    protected static ?string $navigationGroup = 'Data Master';

    // Gunakan konstanta statis untuk cache label
    protected const LABEL = 'Pemasok';
    protected const PLURAL_LABEL = 'Pemasok';

    // Override method untuk menggunakan cache
    public static function getLabel(): string
    {
        return static::LABEL;
    }

    public static function getPluralLabel(): string
    {
        return static::PLURAL_LABEL;
    }

    public static function getNavigationLabel(): string
    {
        return static::PLURAL_LABEL;
    }

    // Tetap gunakan ini untuk kebutuhan lainnya
    protected static ?string $recordTitleAttribute = 'nama_perusahaan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Pemasok')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('nama_perusahaan')
                                    ->label('Nama Perusahaan')
                                    ->required()
                                    ->debounce(500)
                                    ->lazy()
                                    ->maxLength(255),

                                Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon')
                                    ->required()
                                    ->regex('/^[0-9]+$/')
                                    ->minLength(10)
                                    ->maxLength(15)
                                    ->debounce(500)
                                    ->lazy(),

                                Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),
                            ]),
                    ])
                    ->collapsible(),

                Components\Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()?->id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Pemasok::query()->where('id_pemilik', Filament::auth()?->id()))
            ->columns([
                TextColumn::make('nama_perusahaan')
                    ->label('Nama Perusahaan')
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
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Pemasok')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('nama_perusahaan')
                                            ->label('Nama Perusahaan'),

                                        TextEntry::make('no_telp')
                                            ->label('Nomor Telepon'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('alamat')
                                            ->label('Alamat'),

                                        TextEntry::make('created_at')
                                            ->label('Dibuat pada')
                                            ->formatStateUsing(
                                                fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i')
                                            ),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        if ($page instanceof Pages\EditPemasok) {
            return [];
        }

        return $page->generateNavigationItems([
            Pages\ViewPemasok::class,
            Pages\RiwayatTransaksi::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPemasoks::route('/'),
            'create' => Pages\CreatePemasok::route('/create'),
            'edit' => Pages\EditPemasok::route('/{record}/edit'),
            'view' => Pages\ViewPemasok::route('/{record}'),
            'riwayat-transaksi' => Pages\RiwayatTransaksi::route('/{record}/riwayat-transaksi'),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
