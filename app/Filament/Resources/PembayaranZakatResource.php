<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranZakatResource\Pages;
use App\Filament\Resources\PembayaranZakatResource\Pages\BayarZakat;
use App\Filament\Resources\PembayaranZakatResource\RelationManagers;
use App\Models\Pelanggan;
use App\Models\PembayaranZakat;
use App\Models\Penjualan;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class PembayaranZakatResource extends Resource
{
    protected static ?string $model = Penjualan::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $label = 'Pembayaran Zakat';
    protected static ?string $pluralLabel = 'Pembayaran Zakat';
    protected static ?string $navigationLabel = 'Pembayaran Zakat';
    protected static ?string $navigationGroup = 'Zakat';
    protected static ?int $navigationSort = 5;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function canCreate(): bool
    {
        return false; // Ini akan menghilangkan tombol create
    }
    public static function form(Form $form): Form
    {
        return $form->schema([
            Select::make('id_penerima_zakat')
                ->label('Nama Penerima Zakat')
                ->options(\App\Models\PenerimaZakat::all()->pluck('nama_penerima', 'id_penerima_zakat'))
                ->required(),

            Select::make('id_metode_pembayaran')
                ->label('Metode Pembayaran')
                ->options([
                    'tunai' => 'Tunai',
                    'transfer' => 'Transfer',
                ])
                ->required(),

            TextInput::make('modal_terjual')
                ->label('Total Modal')
                ->numeric()
                ->disabled()
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state ?? 0, 0, ',', '.')),

            TextInput::make('nominal_zakat')
                ->label('Total Zakat (2.5%)')
                ->numeric()
                ->disabled()
                ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state ?? 0, 0, ',', '.')),
        ]);
    }



    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => $query->whereNull('id_bayar_zakat')) // Only show unpaid
            ->header(
                fn($livewire) => view('filament.tables.pembayaran-zakat-summary', [
                    'selectedCount' => $livewire->selectedCount,
                    'totalModal' => $livewire->totalModal,
                    'totalZakat' => $livewire->totalZakat,
                ])
            )
            ->columns([
                TextColumn::make('id_penjualan')->label('Nomor Invoice')->searchable()->sortable(),
                TextColumn::make('modalTerjual')
                    ->label('Modal Terjual')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                TextColumn::make('zakat')
                    ->label('Zakat (2.5%)')
                    ->formatStateUsing(fn($state) => 'Rp. ' . number_format($state, 0, ',', '.')),
                TextColumn::make('created_at')->label('Tanggal')
                    ->formatStateUsing(fn($state) => \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i'))
                    ->sortable(),
            ])
            ->filters([
                Filter::make('tanggal_penjualan')
                    ->form([
                        DatePicker::make('from')->label('Dari Tanggal'),
                        DatePicker::make('until')->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['from'], fn($q) => $q->whereDate('tanggal_penjualan', '>=', $data['from']))
                            ->when($data['until'], fn($q) => $q->whereDate('tanggal_penjualan', '<=', $data['until']));
                    }),
            ])
            ->actions([
                // 
            ])
            ->bulkActions([
                BulkAction::make('bayar_zakat')
                    ->label('Bayar Zakat')
                    ->action(fn(Collection $records) => redirect(
                        PembayaranZakatResource::getUrl('create', [
                            'recordIds' => $records->pluck('id_penjualan')->toArray(),
                        ])
                    )),
                BulkAction::make('hitung_total')
                    ->label('Hitung Total')
                    ->action(function (Collection $records, $livewire) {
                        $livewire->selectedCount = $records->count();
                        $livewire->totalModal = $records->sum->modalTerjual;
                        $livewire->totalZakat = $records->sum->zakat;
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
            'index' => Pages\ListPembayaranZakats::route('/'),
            'create' => Pages\CreatePembayaranZakat::route('/create'),
        ];
    }
}
