<?php

namespace App\Filament\Resources\ArusKeuanganResource\Pages;

use App\Filament\Resources\ArusKeuanganResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListArusKeuangans extends ListRecords
{
    protected static string $resource = ArusKeuanganResource::class;
    protected static ?string $title = 'Daftar Arus Keuangan';

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Arus Keuangan'),
        ];
    }

    public function getActiveTab(): string
    {
        return $this->activeTab ?? 'Semua';
    }

    public function getTableFilters(): array
    {
        return $this->tableFilters ?? [];
    }

    public function getTabs(): array
    {
        return [
            'Semua' => Tab::make('Semua')
                ->icon('heroicon-o-bars-3-bottom-left')
                ->modifyQueryUsing(fn(Builder $query) => $query->with('pembayaran')),

            'Tunai' => Tab::make('Tunai')
                ->icon('heroicon-o-banknotes')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->with('pembayaran')
                    ->whereHas('pembayaran', function ($q) {
                        $q->where('jenis_pembayaran', 'tunai');
                    })),

            'Transfer' => Tab::make('Transfer')
                ->icon('heroicon-o-credit-card')
                ->modifyQueryUsing(fn(Builder $query) => $query
                    ->with('pembayaran')
                    ->whereHas('pembayaran', function ($q) {
                        $q->where('jenis_pembayaran', 'transfer');
                    })),
        ];
    }

    protected function getTableQuery(): Builder
    {
        $query = parent::getTableQuery();
        return $query->orderBy('created_at', 'asc')
            ->orderBy('id_arus_keuangan', 'asc');
    }
}
