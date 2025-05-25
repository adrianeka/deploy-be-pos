<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ArusKeuanganResource\Pages;
use App\Models\ArusKeuangan;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components;
use App\Models\TipeTransfer;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\DatePicker;
use Illuminate\Support\Carbon;

class ArusKeuanganResource extends Resource
{
    protected static ?string $model = ArusKeuangan::class;
    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $label = 'Arus Keuangan';
    protected static ?string $pluralLabel = 'Arus Keuangan';
    protected static ?string $navigationLabel = 'Arus Keuangan';
    protected static ?string $slug = 'arus-keuangan';
    protected static ?int $navigationSort = 3;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('id_pemilik', Filament::auth()->id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Components\Section::make('Form Kasir')
                    ->schema([
                        Components\Grid::make(2)
                            ->schema([
                                Select::make('jenis_transaksi')
                                    ->label('Jenis Transaksi')
                                    ->required()
                                    ->options([
                                        'debit' => 'Pemasukan',
                                        'kredit' => 'Pengeluaran',
                                    ])
                                    ->native(false)
                                    ->reactive(),

                                Select::make('metode_pembayaran')
                                    ->label('Metode Pembayaran')
                                    ->required()
                                    ->options([
                                        'tunai' => 'Tunai',
                                        'transfer' => 'Transfer',
                                    ])
                                    ->native(false)
                                    ->reactive(),

                                Select::make('tipe_pembayaran')
                                    ->label('Tipe Pembayaran')
                                    ->options([
                                        'bank' => 'Bank',
                                        'e-wallet' => 'E-wallet',
                                    ])
                                    ->required()
                                    ->reactive()
                                    ->visible(fn($get) => $get('metode_pembayaran') === 'transfer'),

                                Select::make('id_tipe_transfer')
                                    ->label('Jenis Transfer')
                                    ->options(function ($get) {
                                        $tipe = $get('tipe_pembayaran');
                                        return $tipe ? TipeTransfer::getOpsiByMetodeTransfer($tipe) : [];
                                    })
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->visible(
                                        fn($get) =>
                                        $get('metode_pembayaran') === 'transfer' &&
                                            in_array($get('tipe_pembayaran'), ['bank', 'e-wallet'])
                                    ),

                                TextInput::make('nominal')
                                    ->label('Nominal')
                                    ->required()
                                    ->minValue(0)
                                    ->prefix('Rp. '),

                                TextInput::make('keterangan')
                                    ->label('Keterangan (Opsional)')
                                    ->maxLength(255),
                            ])
                            ->columns(2)
                            ->columnSpan(2),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')
                    ->label('Tanggal Pembelian')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('keterangan')
                    ->label('Keterangan'),

                TextColumn::make('nominal_debit')
                    ->label('Debit')
                    ->formatStateUsing(fn($state) => is_numeric($state)
                        ? 'Rp. ' . number_format((int) $state, 0, ',', '.')
                        : '-'),

                TextColumn::make('nominal_kredit')
                    ->label('Kredit')
                    ->formatStateUsing(fn($state) => is_numeric($state)
                        ? 'Rp. ' . number_format((int) $state, 0, ',', '.')
                        : '-'),

                TextColumn::make('saldo')
                    ->label('Saldo')
                    ->formatStateUsing(fn($state) => is_numeric($state)
                        ? 'Rp. ' . number_format((int) $state, 0, ',', '.')
                        : '-')
                    ->state(function ($record, $livewire) {
                        $activeTab = $livewire->getActiveTab() ?? 'Semua';
                        $tableFilters = $livewire->getTableFilters();
                        $dateFrom = $tableFilters['created_at']['created_from'] ?? null;
                        $dateUntil = $tableFilters['created_at']['created_until'] ?? null;

                        return $record->calculateRunningBalance($activeTab, $dateFrom, $dateUntil);
                    }),

            ])
            ->filters([
                Tables\Filters\Filter::make('created_at')
                    ->form([
                        DatePicker::make('created_from')
                            ->label('Dari Tanggal'),
                        DatePicker::make('created_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'] ?? null,
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['created_from'] ?? null) {
                            $indicators['created_from'] = 'Dari tanggal ' . Carbon::parse($data['created_from'])->translatedFormat('d M Y');
                        }
                        if ($data['created_until'] ?? null) {
                            $indicators['created_until'] = 'Sampai tanggal ' . Carbon::parse($data['created_until'])->translatedFormat('d M Y');
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
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
            'index' => Pages\ListArusKeuangans::route('/'),
            'create' => Pages\CreateArusKeuangan::route('/create'),
        ];
    }
}
