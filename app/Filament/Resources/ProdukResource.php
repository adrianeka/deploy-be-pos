<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProdukResource\Pages;
use App\Models\Produk;
use App\Models\Kategori;
use App\Models\Satuan;
use App\Models\LevelHarga;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ImageEntry;

class ProdukResource extends Resource
{
    protected static ?string $model = Produk::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $recordTitleAttribute = 'nama_produk';

    protected static ?string $navigationLabel = 'Produk';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('nama_produk')
                    ->required()
                    ->maxLength(255),

                Select::make('id_kategori')
                    ->relationship('kategori', 'nama_kategori')
                    ->searchable()
                    ->preload()
                    ->nullable()
                    ->createOptionForm([
                        TextInput::make('nama_kategori')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->editOptionForm([
                        TextInput::make('nama_kategori')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->suffixAction(
                        Action::make('hapusKategori')
                            ->label('Hapus')
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Hapus Kategori')
                            ->modalDescription(
                                fn($state) =>
                                Kategori::find($state)
                                    ? "Apakah Anda yakin ingin menghapus kategori '" . Kategori::find($state)->nama_kategori . "'?"
                                    : "Kategori tidak ditemukan."
                            )
                            ->hidden(fn($state) => !$state) // Sembunyikan tombol jika tidak ada kategori yang dipilih
                            ->action(function ($state, $set) {
                                if ($kategori = Kategori::find($state)) {
                                    $kategori->delete();
                                    $set('id_kategori', null); // Reset dropdown ke default
                                }
                            })
                    ),

                FileUpload::make('gambar')
                    ->directory('produks')
                    ->lazy()
                    ->image(),

                Select::make('id_satuan')
                    ->relationship('satuan', 'nama_satuan')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->createOptionForm([
                        TextInput::make('nama_satuan')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->editOptionForm(fn($state) => [
                        TextInput::make('nama_satuan')
                            ->required()
                            ->maxLength(255)
                            ->disabled(fn() => in_array(Satuan::find($state)?->nama_satuan, ['Pcs', 'Kg', 'Liter'])),
                    ])
                    ->suffixAction(
                        Action::make('hapusSatuan')
                            ->label('Hapus')
                            ->icon('heroicon-o-trash')
                            ->requiresConfirmation()
                            ->modalHeading('Hapus Satuan')
                            ->modalDescription(
                                fn($state) =>
                                Satuan::find($state)
                                    ? "Apakah Anda yakin ingin menghapus satuan '" . Satuan::find($state)->nama_satuan . "'? Produk yang menggunakannya akan dipindahkan ke 'Pcs'."
                                    : "Satuan tidak ditemukan."
                            )
                            ->hidden(fn($state) => !$state || in_array(Satuan::find($state)?->nama_satuan, ['Pcs', 'Kg', 'Liter'])) // Sembunyikan tombol jika satuan adalah Pcs, Kg, atau Liter
                            ->action(function ($state, $set) {
                                $satuan = Satuan::find($state);
                                $pcsSatuan = Satuan::where('nama_satuan', 'Pcs')->first(); // Ambil ID satuan Pcs

                                if ($satuan && $pcsSatuan) {
                                    // Update produk yang menggunakan satuan ini ke Pcs
                                    Produk::where('id_satuan', $satuan->id_satuan)->update([
                                        'id_satuan' => $pcsSatuan->id_satuan,
                                    ]);

                                    // Hapus satuan setelah semua produk diperbarui
                                    $satuan->delete();
                                    $set('id_satuan', $pcsSatuan->id_satuan); // Set dropdown ke Pcs
                                }
                            })
                    ),

                TextInput::make('harga_beli')
                    ->numeric()
                    ->required(),

                TextInput::make('harga_jual')
                    ->numeric()
                    ->required()
                    ->label('Harga Jual')
                    ->hidden(fn($record) => $record !== null),

                // Tampilkan hanya saat mengedit produk
                Forms\Components\Repeater::make('levelHargas')
                    ->relationship('levelHargas')
                    ->schema([
                        TextInput::make('nama_level')
                            ->required()
                            ->label('Nama Level Harga'),

                        TextInput::make('harga_jual')
                            ->numeric()
                            ->required()
                            ->label('Harga Jual Level'),

                        Forms\Components\Checkbox::make('is_applied')
                            ->label('Gunakan Level Harga ini')
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                if ($state) {
                                    // Reset semua level harga menjadi tidak aktif
                                    LevelHarga::where('id_produk', $record->id_produk)
                                        ->update(['is_applied' => false]);

                                    // Set level harga ini sebagai yang dipilih
                                    $set('is_applied', true);

                                    // Update harga jual di tabel produk
                                    $record->update(['harga_jual' => $get('harga_jual')]);
                                }
                            }),
                    ])
                    ->minItems(0)
                    ->maxItems(5)
                    ->hidden(fn() => request()->routeIs('filament.admin.resources.produks.create')),

                TextInput::make('stok')
                    ->numeric()
                    ->required(),

                Textarea::make('deskripsi')
                    ->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                ImageColumn::make('gambar'),
                TextColumn::make('nama_produk')
                    ->sortable()
                    ->searchable(),

                // Harga jual yang sedang digunakan
                TextColumn::make('appliedLevelHarga.harga_jual')
                    ->label('Harga Jual')
                    ->sortable()
                    ->money('RP.'),

                TextColumn::make('stok')
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Split::make([
                            // Bagian kiri: Memuat Grid dengan dua kolom
                            Grid::make(2)
                                ->schema([
                                    // Kolom 1 dalam Grid
                                    Group::make([
                                        TextEntry::make('nama_produk'),
                                        TextEntry::make('kategori.nama_kategori')
                                            ->formatStateUsing(fn($state) => $state !== null ? $state : '-'),
                                        TextEntry::make('satuan.nama_satuan'),
                                    ]),
                                    // Kolom 2 dalam Grid
                                    Group::make([
                                        TextEntry::make('harga_beli')
                                            ->money('RP.'),
                                        TextEntry::make('appliedLevelHarga.harga_jual')
                                            ->label('Harga Jual')
                                            ->money('RP.'),
                                        TextEntry::make('stok'),
                                    ]),
                                ]),

                            // Bagian kanan: Gambar produk
                            ImageEntry::make('gambar')
                                ->hiddenLabel()
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
            'create' => Pages\CreateProduk::route('/create'),
            'edit' => Pages\EditProduk::route('/{record}/edit'),
            'view' => Pages\ViewProduk::route('/{record}'),
        ];
    }
}
