<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasirResource\Pages;
use App\Models\Kasir;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Model;
use Filament\Pages\Page;
use Filament\Pages\SubNavigationPosition;

class KasirResource extends Resource
{
    protected static ?string $model = Kasir::class;
    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $label = 'Kasir';
    protected static ?string $recordTitleAttribute = 'nama';
    protected static ?string $pluralLabel = 'Kasir';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $navigationGroup = 'Data Master';
    protected static ?int $navigationSort = 0;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Kasir')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Components\TextInput::make('nama')
                                    ->label('Nama')
                                    ->required()
                                    ->regex('/^[A-Za-z.\s]+$/')
                                    ->debounce(500)
                                    ->lazy()
                                    ->maxLength(255),

                                Components\TextInput::make('email')
                                    ->label('Email')
                                    ->required()
                                    ->email()
                                    ->debounce(500)
                                    ->lazy()
                                    ->unique(
                                        table: User::class,
                                        column: 'email',
                                        ignoreRecord: true,
                                        modifyRuleUsing: fn($rule, $record) =>
                                        $record?->user ? $rule->ignore($record->user->id) : $rule
                                    )
                                    ->formatStateUsing(fn($record) => $record?->user?->email)
                                    ->placeholder('Email kasir yang akan digunakan login'),

                                Components\TextInput::make('no_telp')
                                    ->label('Nomor Telepon')
                                    ->required()
                                    ->regex('/^[0-9]+$/')
                                    ->minLength(10)
                                    ->maxLength(13)
                                    ->lazy()
                                    ->debounce(500),

                                Components\TextInput::make('alamat')
                                    ->label('Alamat')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpanFull(),

                                Components\TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->required(fn($context) => $context === 'create')
                                    ->dehydrated(fn($state) => filled($state))
                                    ->maxLength(255)
                                    ->columnSpan(1),

                                Components\TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable()
                                    ->same('password')
                                    ->visible(fn($context) => $context === 'create')
                                    ->dehydrated(false)
                                    ->required(fn($context) => $context === 'create')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->collapsible(),

                Components\Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()?->id()),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(
                fn() =>
                Kasir::query()
                    ->with('user') // eager load user to avoid N+1
                    ->where('id_pemilik', Filament::auth()->id())
            )
            ->columns([
                TextColumn::make('nama')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('no_telp')
                    ->label('Nomor Telepon')
                    ->searchable()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        if ($record->user) {
                            $record->user->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->before(function ($records) {
                        foreach ($records as $record) {
                            if ($record->user) {
                                $record->user->delete();
                            }
                        }
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Kasir')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('nama')
                                            ->label('Nama'),
                                        TextEntry::make('user.email')
                                            ->label('Email'),
                                        TextEntry::make('no_telp')
                                            ->label('Nomor Telepon')
                                    ]),
                                    Group::make([
                                        TextEntry::make('alamat')
                                            ->label('Alamat'),
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

    public static function getRecordSubNavigation(Page $page): array
    {
        if ($page instanceof Pages\EditKasir) {
            return [];
        }

        return $page->generateNavigationItems([
            Pages\ViewKasir::class,
            Pages\RiwayatTransaksi::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasirs::route('/'),
            'create' => Pages\CreateKasir::route('/create'),
            'edit' => Pages\EditKasir::route('/{record}/edit'),
            'view' => Pages\ViewKasir::route('/{record}'),
            'riwayat-transaksi' => Pages\RiwayatTransaksi::route('/{record}/riwayat-transaksi'),
        ];
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
