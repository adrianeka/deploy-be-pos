<?php

namespace App\Filament\Widgets;

use App\Models\Kasir;
use App\Models\Pelanggan;
use App\Models\Pemasok;
use App\Models\PenerimaZakat;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Filament::auth()->id();

        $stats = [
            [
                'label' => 'Kasir',
                'value' => Kasir::where('id_pemilik', $userId)->count(),
                'description' => 'Total kasir aktif',
                'descriptionIcon' => 'heroicon-o-user-group',
                'color' => 'primary',
                'icon' => 'heroicon-s-user-plus',
                'chart' => [3, 6, 5, 8, 6, 10, 12],
                'url' => route('filament.admin.resources.data-master.kasir.index'),
            ],
            [
                'label' => 'Pelanggan',
                'value' => Pelanggan::where('id_pemilik', $userId)->count(),
                'description' => 'Total pelanggan terdaftar',
                'descriptionIcon' => 'heroicon-o-users',
                'color' => 'success',
                'icon' => 'heroicon-s-user-group',
                'chart' => [15, 4, 10, 2, 12, 4, 12],
                'url' => route('filament.admin.resources.data-master.pelanggan.index'),
            ],
            [
                'label' => 'Penerima Zakat',
                'value' => PenerimaZakat::where('id_pemilik', $userId)->count(),
                'description' => 'Penerima manfaat zakat',
                'descriptionIcon' => 'heroicon-o-heart',
                'color' => 'warning',
                'icon' => 'heroicon-s-hand-raised',
                'chart' => [2, 10, 5, 8, 3, 7, 2],
                'url' => route('filament.admin.resources.data-master.penerima-zakat.index'),
            ],
            [
                'label' => 'Pemasok',
                'value' => Pemasok::where('id_pemilik', $userId)->count(),
                'description' => 'Mitra pemasok produk',
                'descriptionIcon' => 'heroicon-o-truck',
                'color' => 'info',
                'icon' => 'heroicon-s-building-storefront',
                'chart' => [3, 5, 8, 2, 10, 5, 4],
                'url' => route('filament.admin.resources.data-master.pemasok.index'),
            ],
        ];

        return collect($stats)->map(function ($item) {
            return Stat::make($item['label'], $item['value'])
                ->description($item['description'])
                ->descriptionIcon($item['descriptionIcon'])
                // ->color($item['color'])
                ->icon($item['icon'])
                ->chart($item['chart'])
                ->url($item['url'])
                ->openUrlInNewTab()
                ->extraAttributes([
                    'class' => 'cursor-pointer hover:shadow-xl transition-all duration-300 ease-in-out rounded-xl'
                ]);
        })->toArray();
    }
}
