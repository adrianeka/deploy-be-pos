<div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
    @if($selectedCount > 0)
        <div class="space-y-1 text-gray-700 dark:text-gray-300 font-medium text-sm">
            <div class="flex">
                <div class="w-32">Terpilih</div>
                <div>: {{ $selectedCount }} Transaksi</div>
            </div>
            <div class="flex">
                <div class="w-32">Total Modal</div>
                <div>: <span class="font-bold">Rp. {{ number_format($totalModal, 0, ',', '.') }}</span></div>
            </div>
            <div class="flex">
                <div class="w-32">Total Zakat</div>
                <div>: <span class="font-bold">Rp. {{ number_format($totalZakat, 0, ',', '.') }}</span></div>
            </div>
        </div>
    @else
        <p class="text-gray-500 dark:text-gray-400">Pilih transaksi untuk melihat total</p>
    @endif
</div>
