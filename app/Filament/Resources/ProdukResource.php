<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\Satuan;
use Filament\Forms\Components\Hidden;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Checkbox;
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
use Illuminate\Validation\ValidationException;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\ActionGroup;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $recordTitleAttribute = 'nama_produk';
    protected static ?string $navigationLabel = 'Produk';
    protected static ?string $navigationGroup = 'Data Master';

    public static function form(Form $form): Form
    {
        $isCreateOperation = !$form->getOperation() || $form->getOperation() === 'create';

        $schema = [
            TextInput::make('nama_produk')
                ->label('Nama Produk')
                ->required()
                ->maxLength(255),

            Select::make('id_kategori')
                ->relationship('kategori', 'nama_kategori', function ($query) {
                    // Filter untuk menampilkan satuan milik user yang login atau yang tidak memiliki pemilik
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
                ->directory('produks')
                ->lazy()
                ->image(),

            Select::make('id_satuan')
                ->relationship('satuan', 'nama_satuan', function ($query) {
                    // Filter untuk menampilkan satuan milik user yang login atau yang tidak memiliki pemilik
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
                ->integer()
                ->required(),

            TextInput::make('stok_minimum')
                ->label('Stok Minimum')
                ->integer()
                ->required(),

            Textarea::make('deskripsi')
                ->label('Deskripsi')
                ->nullable(),

            Hidden::make('id_pemilik')
                ->default(fn() => Filament::auth()->id())
                ->dehydrated(true),
        ];

        // Only add harga_jual for create operations
        if ($isCreateOperation) {
            $schema[] = TextInput::make('harga_jual')
                ->label('Harga Jual')
                ->integer()
                ->required();
        }

        return $form->schema($schema);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->query(fn() => Produk::query()->where('id_pemilik', Filament::auth()->user()->id)) // Filter berdasarkan user login
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

                TextColumn::make('level_hargas.harga_jual')
                    ->label('Harga Jual')
                    ->getStateUsing(function ($record) {
                        // Find the level_harga with nama_level = 'Standard'
                        $standardLevel = $record->level_hargas()
                            ->where('nama_level', 'Standard')
                            ->first();

                        return $standardLevel ? $standardLevel->harga_jual : null;
                    })
                    ->formatStateUsing(fn($state) => $state ? 'Rp. ' . number_format($state, 0, ',', '.') : '-')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('stok_minimum')
                    ->label('Stok Minimum')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([])
            ->actions([
                ActionGroup::make([
                    Tables\Actions\ViewAction::make()
                        ->label('View Produk')
                        ->icon('heroicon-o-eye'),

                    Tables\Actions\EditAction::make()
                        ->label('Edit Produk')
                        ->modalHeading('Edit Produk')
                        ->modalSubmitActionLabel('Simpan Perubahan')
                        ->modalCancelActionLabel('Batal')
                        ->icon('heroicon-o-pencil'),

                    Tables\Actions\Action::make('editHarga')
                        ->label('Edit Level Harga')
                        ->icon('heroicon-o-currency-dollar')
                        ->modalHeading('Edit Level Harga')
                        ->modalSubmitActionLabel('Simpan Perubahan')
                        ->modalCancelActionLabel('Batal')
                        ->form([
                            Repeater::make('level_hargas')
                                ->label('Level Harga')
                                ->schema([
                                    TextInput::make('nama_level')
                                        ->label('Nama Level')
                                        ->required()
                                        ->disabled(fn($state) => ($state['nama_level'] ?? '') === 'Standard')
                                        ->maxLength(255),
                                    TextInput::make('harga_jual')
                                        ->label('Harga Jual')
                                        ->integer()
                                        ->required(),
                                    Hidden::make('id_level_harga'),
                                    Hidden::make('is_standard'),
                                ])
                                ->defaultItems(0)
                                ->addActionLabel('Tambah Level Harga')
                                ->deleteAction(
                                    fn($action) => $action->hidden(fn($record) => $record['is_standard'] ?? false)
                                )
                                ->reorderable(false)
                                ->columnSpanFull(),
                        ])
                        ->fillForm(function ($record) {
                            // Load existing price levels
                            $existingLevels = $record->level_hargas()->get()->map(function ($level) {
                                return [
                                    'nama_level' => $level->nama_level,
                                    'harga_jual' => $level->harga_jual,
                                    'id_level_harga' => $level->id_level_harga,
                                    'is_standard' => $level->nama_level === 'Standard',
                                ];
                            })->toArray();

                            return [
                                'level_hargas' => $existingLevels,
                            ];
                        })
                        ->action(function (array $data, $record): void {
                            // Initialize empty array if level_hargas is not set
                            $levelData = $data['level_hargas'] ?? [];

                            // Keep track of processed IDs to handle deletions later
                            $processedIds = [];

                            foreach ($levelData as $level) {
                                try {
                                    if (!empty($level['id_level_harga'])) {
                                        // Update existing level
                                        $record->level_hargas()->where('id_level_harga', $level['id_level_harga'])->update([
                                            'nama_level' => $level['nama_level'],
                                            'harga_jual' => $level['harga_jual'],
                                        ]);
                                        $processedIds[] = $level['id_level_harga'];
                                    } else {
                                        // Create new level
                                        $newLevel = $record->level_hargas()->create([
                                            'nama_level' => $level['nama_level'],
                                            'harga_jual' => $level['harga_jual'],
                                            // No need for id_pemilik as we can get it from the product
                                        ]);
                                        $processedIds[] = $newLevel->id_level_harga;
                                    }
                                } catch (\Exception $e) {
                                    // Log the error and continue
                                    Notification::make()
                                        ->title('Error processing level: ' . $e->getMessage())
                                        ->danger()
                                        ->send();
                                }
                            }

                            // Delete levels that were removed (except Standard)
                            $record->level_hargas()
                                ->whereNotIn('id_level_harga', $processedIds)
                                ->where('nama_level', '!=', 'Standard') // Protect Standard level from deletion
                                ->delete();

                            // Notifikasi sukses
                            Notification::make()
                                ->title('Level harga berhasil diperbarui')
                                ->success()
                                ->send();
                        }),

                    Tables\Actions\DeleteAction::make()
                        ->icon('heroicon-o-trash'),
                ])
                    ->label('Aksi')
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->size('sm')
                    ->color('gray'),
            ])
            ->bulkActions([
                BulkAction::make('delete_selected')
                    ->label('Hapus yang Dipilih')
                    ->action(fn($records) => $records->each->delete())
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Tambah Produk')
                    ->modalHeading('Tambah Produk Baru')
                    ->modalDescription('Silakan isi informasi untuk menambahkan produk baru.')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->using(function (array $data): Produk {
                        // Create the product
                        $product = Produk::create($data);

                        // Run the afterCreate method
                        self::afterCreate($product, $data);

                        return $product;
                    }),
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
                                                // Find the level_harga with nama_level = 'Standard'
                                                $standardLevel = $record->level_hargas()
                                                    ->where('nama_level', 'Standard')
                                                    ->first();

                                                return $standardLevel ? $standardLevel->harga_jual : null;
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
                    ]),

                Section::make('Deskripsi Produk')
                    ->schema([
                        TextEntry::make('deskripsi')
                            ->prose()
                            ->markdown()
                            ->hiddenLabel(),
                    ])
                    ->collapsible(),
            ]);
    }


    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProduks::route('/'),
        ];
    }

    // Helpers dan komponene yang dipisahkan

    private static function isSatuanDefault(?string $namaSatuan): bool
    {
        return in_array($namaSatuan, ['Pcs', 'Kg', 'Liter']);
    }

    public static function afterCreate(Produk $record, array $data): void
    {
        // Gunakan nilai default jika harga_jual tidak ada
        $hargaJual = $data['harga_jual'];

        // Pastikan harga jual selalu integer
        $hargaJual = intval($hargaJual);

        // Buat Level Harga Standar
        $levelHarga = $record->level_hargas()->create([
            'nama_level' => 'Standard',
            'harga_jual' => $hargaJual,
            'id_pemilik' => Filament::auth()->id(),
        ]);

        // Terapkan harga level harga standar sebagai harga jual produk
        $record->update([
            'harga_jual' => $levelHarga->harga_jual,
        ]);
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
}
