<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Satuan;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ActionGroup;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Filament\Forms\Components\TextInput\Mask;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $recordTitleAttribute = 'nama_produk';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $pluralLabel = 'Produk';
    protected static ?string $slug = 'inventaris/produk';
    protected static ?string $navigationGroup = 'Inventaris';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        $isCreateOperation = !$form->getOperation() || $form->getOperation() === 'create';

        return $form
            ->schema([
                Components\Section::make('Form Produk')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema(array_filter([
                                TextInput::make('nama_produk')
                                    ->label('Nama Produk')
                                    ->required()
                                    ->maxLength(255),

                                Select::make('id_kategori')
                                    ->relationship('kategori', 'nama_kategori', function ($query) {
                                        return $query->where(function ($query) {
                                            $query->where('id_pemilik', Filament::auth()->id())
                                                ->orWhereNull('id_pemilik');
                                        });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->createOptionForm([
                                        TextInput::make('nama_kategori')
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(255),
                                        Hidden::make('id_pemilik')
                                            ->default(fn() => Filament::auth()->id()),
                                    ])
                                    ->editOptionForm([
                                        TextInput::make('nama_kategori')
                                            ->label('Nama Kategori')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->suffixAction(self::getHapusKategoriAction()),

                                FileUpload::make('foto_produk')
                                    ->label('Foto Produk')
                                    ->directory('produk')
                                    ->lazy()
                                    ->image(),

                                Select::make('id_satuan')
                                    ->relationship('satuan', 'nama_satuan', function ($query) {
                                        return $query->where(function ($query) {
                                            $query->where('id_pemilik', Filament::auth()->id())
                                                ->orWhereNull('id_pemilik');
                                        });
                                    })
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->createOptionForm([
                                        TextInput::make('nama_satuan')
                                            ->label('Nama Satuan')
                                            ->required()
                                            ->maxLength(255),
                                        Hidden::make('id_pemilik')
                                            ->default(fn() => Filament::auth()->id()),
                                    ])
                                    ->editOptionForm(fn($state) => [
                                        TextInput::make('nama_satuan')
                                            ->label('Nama Satuan')
                                            ->required()
                                            ->maxLength(255)
                                            ->disabled(fn() => self::isSatuanDefault(Satuan::find($state)?->nama_satuan)),
                                    ])
                                    ->suffixAction(self::getHapusSatuanAction()),

                                TextInput::make('harga_beli')
                                    ->label('Harga Beli')
                                    ->prefix('Rp')
                                    ->integer()
                                    ->required(),

                                $isCreateOperation ? TextInput::make('harga_jual')
                                    ->label('Harga Jual (Standart)')
                                    ->integer()
                                    ->required() : null,

                                TextInput::make('stok_minimum')
                                    ->label('Stok Minimum')
                                    ->integer()
                                    ->required(),

                                Textarea::make('deskripsi')
                                    ->label('Deskripsi')
                                    ->nullable(),
                            ]))
                    ])
                    ->collapsible(),
                Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()->id())
                    ->dehydrated(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Produk::query()->where('id_pemilik', Filament::auth()->user()->id))
            ->columns([
                ImageColumn::make('foto_produk')
                    ->getStateUsing(
                        fn($record) =>
                        $record->foto_produk
                            ? asset('storage/' . $record->foto_produk)
                            : 'https://ui-avatars.com/api/?name=' . urlencode(substr($record->nama_produk, 0, 2)) . '&size=100'
                    )
                    ->size(100),

                TextColumn::make('nama_produk')
                    ->label('Nama Produk')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('harga_beli')
                    ->label('Harga Beli')
                    ->sortable()
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->searchable(),

                TextColumn::make('level_hargas.harga_jual')
                    ->label('Harga Jual')
                    ->getStateUsing(function ($record) {
                        $standartLevel = $record->level_hargas()
                            ->where('nama_level', 'Standart')
                            ->first();

                        return $standartLevel ? $standartLevel->harga_jual : null;
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('satuan.nama_satuan')
                    ->label('Satuan')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_kategori')
                    ->label('Kategori')
                    ->options(function () {
                        return Kategori::where(function ($query) {
                            $query->where('id_pemilik', Filament::auth()->id())
                                ->orWhereNull('id_pemilik');
                        })->pluck('nama_kategori', 'id_kategori')->toArray();
                    })
                    ->preload()
                    ->multiple(),

                Tables\Filters\SelectFilter::make('id_satuan')
                    ->label('Satuan')
                    ->options(function () {
                        return Satuan::where(function ($query) {
                            $query->where('id_pemilik', Filament::auth()->id())
                                ->orWhereNull('id_pemilik');
                        })->pluck('nama_satuan', 'id_satuan')->toArray();
                    })
                    ->preload()
                    ->multiple(),
            ])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\Action::make('editHarga')
                        ->label('Edit Level Harga')
                        ->icon('heroicon-o-currency-dollar')
                        ->modalHeading('Edit Level Harga')
                        ->modalSubmitActionLabel('Save changes')
                        ->modalCancelActionLabel('Cancel')
                        ->form([
                            Repeater::make('level_hargas')
                                ->label('Level Harga')
                                ->schema([
                                    TextInput::make('nama_level')
                                        ->label('Nama Level')
                                        ->required()
                                        ->disabled(fn($state) => $state === 'Standart')
                                        ->dehydrated(true)
                                        ->maxLength(255),
                                    TextInput::make('harga_jual')
                                        ->label('Harga Jual')
                                        ->integer()
                                        ->required(),
                                    Hidden::make('id_level_harga'),
                                    Hidden::make('is_standart'),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Tambah Level Harga')
                                ->deleteAction(
                                    fn($action) => $action->hidden(fn($record) => $record['is_standart'] ?? false)
                                )
                                ->reorderable(false)
                                ->columnSpanFull()
                                ->deleteAction(
                                    fn(Action $action) => $action
                                        ->hidden(function (array $arguments, Repeater $component) {
                                            $items = $component->getState();
                                            $activeItem = $items[$arguments['item']] ?? null;
                                            return $activeItem && ($activeItem['nama_level'] === 'Standart');
                                        })
                                ),
                        ])
                        ->fillForm(function ($record) {
                            $existingLevels = $record->level_hargas()->get()->map(function ($level) {
                                return [
                                    'nama_level' => $level->nama_level,
                                    'harga_jual' => $level->harga_jual,
                                    'id_level_harga' => $level->id_level_harga,
                                    'is_standart' => $level->nama_level === 'Standart',
                                ];
                            })->toArray();
                            return [
                                'level_hargas' => $existingLevels,
                            ];
                        })
                        ->action(function (array $data, $record): void {
                            $levelData = $data['level_hargas'] ?? [];
                            $processedIds = [];
                            foreach ($levelData as $level) {
                                try {
                                    if (!empty($level['id_level_harga'])) {
                                        $record->level_hargas()->where('id_level_harga', $level['id_level_harga'])->update([
                                            'nama_level' => $level['nama_level'],
                                            'harga_jual' => $level['harga_jual'],
                                        ]);
                                        $processedIds[] = $level['id_level_harga'];
                                    } else {
                                        $newLevel = $record->level_hargas()->create([
                                            'nama_level' => $level['nama_level'],
                                            'harga_jual' => $level['harga_jual'],
                                        ]);
                                        $processedIds[] = $newLevel->id_level_harga;
                                    }
                                } catch (\Exception $e) {
                                    Notification::make()
                                        ->title('Error processing level: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }
                            $record->level_hargas()
                                ->whereNotIn('id_level_harga', $processedIds)
                                ->where('nama_level', '!=', 'Standart')
                                ->delete();
                            Notification::make()
                                ->title('Level harga berhasil diperbarui')
                                ->success()
                                ->send();
                        }),
                    Tables\Actions\DeleteAction::make()
                ])
            ])
            ->bulkActions([
                DeleteBulkAction::make()
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Data Produk')
                    ->schema([
                        Split::make([
                            Grid::make(2)
                                ->schema([
                                    Group::make([
                                        TextEntry::make('nama_produk')
                                            ->label('Nama Produk'),

                                        TextEntry::make('kategori.nama_kategori')
                                            ->label('Kategori')
                                            ->formatStateUsing(fn($state) => $state !== null ? $state : '-'),

                                        TextEntry::make('satuan.nama_satuan')
                                            ->label('Satuan'),
                                    ]),
                                    Group::make([
                                        TextEntry::make('harga_beli')
                                            ->label('Harga Beli')
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                        TextEntry::make('level_hargas.harga_jual')
                                            ->label('Harga Jual Standart')
                                            ->getStateUsing(function ($record) {
                                                $standartLevel = $record->level_hargas()
                                                    ->where('nama_level', 'Standart')
                                                    ->first();

                                                return $standartLevel ? $standartLevel->harga_jual : null;
                                            })
                                            ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),

                                        TextEntry::make('stok_minimum')
                                            ->label('Stok Minimum'),
                                    ]),
                                ]),

                            ImageEntry::make('foto_produk')
                                ->hiddenLabel()
                                ->getStateUsing(
                                    fn($record) =>
                                    $record->foto_produk
                                        ? asset('storage/' . $record->foto_produk)
                                        : 'https://ui-avatars.com/api/?name=' . urlencode(substr($record->nama_produk, 0, 2)) . '&size=100'
                                )
                                ->size(100)
                                ->grow(false)
                        ])->from('lg'),
                    ])
                    ->collapsible(),
                Section::make('Deskripsi Produk')
                    ->schema([
                        TextEntry::make('deskripsi')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->visible(fn($record) => !is_null($record->deskripsi))
                    ->collapsible(),
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
            'index' => Pages\ListProduks::route('/'),
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
            'view' => Pages\ViewProduk::route('/{record}'),
        ];
    }

    private static function isSatuanDefault(?string $namaSatuan): bool
    {
        return in_array($namaSatuan, ['Pcs', 'Kg', 'Liter']);
    }

    private static function getHapusKategoriAction(): Action
    {
        return Action::make('hapusKategori')
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading('Hapus Kategori')
            ->modalDescription(
                fn($state) => Kategori::find($state)
                    ? "Apakah Anda yakin ingin menghapus kategori '" . Kategori::find($state)->nama_kategori . "'?"
                    : "Kategori tidak ditemukan."
            )
            ->hidden(fn($state) => !$state)
            ->action(function ($state, $set) {
                if ($kategori = Kategori::find($state)) {
                    $kategori->delete();
                    $set('id_kategori', null);
                }
            });
    }

    private static function getHapusSatuanAction(): Action
    {
        return Action::make('hapusSatuan')
            ->label('Hapus')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->modalHeading('Hapus Satuan')
            ->modalDescription(
                fn($state) => Satuan::find($state)
                    ? "Apakah Anda yakin ingin menghapus satuan '" . Satuan::find($state)->nama_satuan . "'? Produk yang menggunakannya akan dipindahkan ke 'Pcs'."
                    : "Satuan tidak ditemukan."
            )
            ->hidden(fn($state) => !$state || self::isSatuanDefault(Satuan::find($state)?->nama_satuan))
            ->action(function ($state, $set) {
                $satuan = Satuan::find($state);
                $pcsSatuan = Satuan::where('nama_satuan', 'Pcs')->first();

                if ($satuan && $pcsSatuan) {
                    Produk::where('id_satuan', $satuan->id_satuan)->update([
                        'id_satuan' => $pcsSatuan->id_satuan,
                    ]);

                    $satuan->delete();
                    $set('id_satuan', $pcsSatuan->id_satuan);
                }
            });
    }

    public static function getGlobalSearchResultUrl(Model $record): string
    {
        return static::getUrl('view', ['record' => $record]);
    }
}
