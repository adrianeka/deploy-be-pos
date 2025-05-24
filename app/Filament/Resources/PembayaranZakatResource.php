<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PembayaranZakatResource\Pages;
use App\Models\Penjualan;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Form;
use Filament\Pages\SubNavigationPosition;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PembayaranZakatResource extends Resource
{
    protected static ?string $model = Penjualan::class;
    protected static ?string $navigationIcon = 'heroicon-o-heart';
    protected static ?string $label = 'Pembayaran Zakat';
    protected static ?string $pluralLabel = 'Pembayaran Zakat';
    protected static ?string $navigationLabel = 'Pembayaran Zakat';
    protected static ?string $navigationGroup = 'Zakat';
    protected static ?string $slug = 'zakat/bayar-zakat';
    protected static ?int $navigationSort = 5;
    protected static SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('kasir', fn($query) => $query->where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik));
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn(Builder $query) => self::applyTableQuery($query))
            ->header(fn($livewire) => self::getTableHeader($livewire))
            ->columns(self::getTableColumns())
            ->filters(self::getTableFilters())
            ->actions([])
            ->bulkActions(self::getBulkActions());
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPembayaranZakats::route('/'),
            'create' => Pages\CreatePembayaranZakat::route('/create'),
        ];
    }

    private static function applyTableQuery(Builder $query): Builder
    {
        return $query
            ->whereNull('id_bayar_zakat')
            ->whereHas('kasir', function ($q) {
                $q->where('id_pemilik', Filament::auth()->user()?->pemilik?->id_pemilik);
            });
    }

    private static function getTableHeader($livewire)
    {
        return view('filament.tables.pembayaran-zakat-summary', [
            'selectedCount' => $livewire->selectedCount,
            'totalModal' => $livewire->totalModal,
            'totalZakat' => $livewire->totalZakat,
        ]);
    }

    private static function getTableColumns(): array
    {
        return [
            TextColumn::make('id_penjualan')
                ->label('Nomor Invoice')
                ->searchable()
                ->sortable(),

            TextColumn::make('modalTerjual')
                ->label('Modal Terjual')
                ->formatStateUsing(fn($state) => self::formatCurrency($state)),

            TextColumn::make('zakat')
                ->label('Zakat (2.5%)')
                ->formatStateUsing(fn($state) => self::formatCurrency($state)),

            TextColumn::make('created_at')
                ->label('Tanggal')
                ->formatStateUsing(fn($state) => self::formatDate($state))
                ->sortable(),
        ];
    }

    private static function getTableFilters(): array
    {
        return [
            Filter::make('created_at')
                ->form([
                    DatePicker::make('from')->label('Dari Tanggal'),
                    DatePicker::make('until')->label('Sampai Tanggal'),
                ])
                ->query(function ($query, array $data) {
                    return $query
                        ->when($data['from'], fn($q) => $q->whereDate('created_at', '>=', $data['from']))
                        ->when($data['until'], fn($q) => $q->whereDate('created_at', '<=', $data['until']));
                }),
        ];
    }

    private static function getBulkActions(): array
    {
        return [
            BulkAction::make('bayar_zakat')
                ->label('Bayar Zakat')
                ->action(fn(Collection $records) => self::redirectToBayarZakat($records)),

            BulkAction::make('hitung_total')
                ->label('Hitung Zakat')
                ->action(fn(Collection $records, $livewire) => self::hitungTotal($records, $livewire)),
        ];
    }

    private static function redirectToBayarZakat(Collection $records)
    {
        return redirect(
            self::getUrl('create', [
                'recordIds' => $records->pluck('id_penjualan')->toArray(),
            ])
        );
    }

    private static function hitungTotal(Collection $records, $livewire): void
    {
        $livewire->selectedCount = $records->count();
        $livewire->totalModal = $records->sum->modalTerjual;
        $livewire->totalZakat = $records->sum->zakat;
    }

    private static function formatCurrency($state): string
    {
        return 'Rp. ' . number_format($state, 0, ',', '.');
    }

    private static function formatDate($state): string
    {
        return \Carbon\Carbon::parse($state)->translatedFormat('d M Y, \\J\\a\\m H:i');
    }
}
