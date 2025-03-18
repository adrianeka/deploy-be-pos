<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KasirResource\Pages;
use App\Models\Kasir;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Group;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Split;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasirResource extends Resource
{
    protected static ?string $model = Kasir::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $label = 'Kasir';
    protected static ?string $pluralLabel = 'Kasir';
    protected static ?string $navigationLabel = 'Kasir';
    protected static ?string $navigationGroup = 'Data Master';

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                TextInput::make('nama')
                    ->label('Nama')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->required()
                    ->email()
                    ->unique(
                        table: User::class,
                        column: 'email',
                        ignoreRecord: true,
                        modifyRuleUsing: function ($rule, $record) {
                            if ($record && $record->user) {
                                return $rule->ignore($record->user->id);
                            }
                            return $rule;
                        }
                    )
                    ->formatStateUsing(fn($record) => $record?->user?->email),

                TextInput::make('no_telp')
                    ->label('Nomor Telepon')
                    ->numeric()
                    ->minLength(10)
                    ->maxLength(13)
                    ->required(),

                TextInput::make('alamat')
                    ->label('Alamat')
                    ->required()
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn($context) => $context === 'create')
                    ->dehydrated(fn($state) => filled($state))
                    ->maxLength(255),

                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->revealable()
                    ->required(fn($context) => $context === 'create')
                    ->same('password')
                    ->visible(fn($context) => $context === 'create'),

                Hidden::make('id_pemilik')
                    ->default(fn() => Filament::auth()->id()),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->query(function () {
                return Kasir::query()->where('id_pemilik', Filament::auth()->id());
            })
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
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->modalHeading("Edit Kasir")
                    ->modalSubmitActionLabel('Simpan Perubahan')
                    ->modalCancelActionLabel('Batal')
                    ->using(function (Kasir $record, array $data): Model {
                        return DB::transaction(function () use ($record, $data) {
                            // Update user data
                            $record->user->update([
                                'name' => $data['nama'],
                                'email' => $data['email'],
                                // Hanya update password jika diisi
                                'password' => !empty($data['password']) ? Hash::make($data['password']) : $record->user->password,
                            ]);

                            // Update kasir data
                            $record->update([
                                'id_pemilik' => $data['id_pemilik'] ?? Filament::auth()->id(),
                                'nama' => $data['nama'],
                                'no_telp' => $data['no_telp'],
                                'alamat' => $data['alamat'],
                            ]);

                            return $record;
                        });
                    }),
                Tables\Actions\DeleteAction::make()
                    ->before(function (Model $record) {
                        // Delete the associated user when deleting the kasir
                        if ($record->user) {
                            $record->user->delete();
                        }
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus yang Dipilih')
                        ->before(function ($records) {
                            // Delete the associated users when bulk deleting kasirs
                            foreach ($records as $record) {
                                if ($record->user) {
                                    $record->user->delete();
                                }
                            }
                        }),
                ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambah Kasir')
                    ->modalHeading('Tambah Kasir Baru')
                    ->modalDescription('Silakan isi informasi untuk menambahkan kasir baru.')
                    ->modalSubmitActionLabel('Simpan')
                    ->modalCancelActionLabel('Batal')
                    ->mutateFormDataUsing(function (array $data): array {
                        // Remove password_confirmation from data
                        unset($data['password_confirmation']);
                        return $data;
                    })
                    ->using(function (array $data): Model {
                        return DB::transaction(function () use ($data) {
                            // First create the user
                            $user = User::create([
                                'name' => $data['nama'],
                                'email' => $data['email'],
                                'password' => Hash::make($data['password']),
                                'role' => 'kasir',
                            ]);

                            // Then create the kasir
                            return Kasir::create([
                                'id_user' => $user->id,
                                'id_pemilik' => $data['id_pemilik'] ?? Filament::auth()->id(),
                                'nama' => $data['nama'],
                                'no_telp' => $data['no_telp'],
                                'alamat' => $data['alamat'],
                            ]);
                        });
                    })
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
                                            ->dateTime(),
                                        TextEntry::make('updated_at')
                                            ->label('Diperbarui pada')
                                            ->dateTime(),
                                    ]),
                                ]),
                        ])->from('lg'),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKasirs::route('/'),
            //'view' => Pages\ViewKasir::route('/{record}'),
        ];
    }
}
