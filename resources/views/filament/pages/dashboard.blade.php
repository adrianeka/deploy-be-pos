<x-filament::page>
    {{-- Row 1: Penjualan Chart --}}
    <div class="mb-6">
        @livewire(App\Filament\Widgets\PenjualanChart::class)
    </div>

    {{-- Row 2: Pembelian Chart --}}
    <div class="mb-6">
        @livewire(App\Filament\Widgets\PembelianChart::class)
    </div>
</x-filament::page>
