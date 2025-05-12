<div class="px-4 py-2 bg-gray-50 dark:bg-gray-800 rounded-lg">
    @if($selectedCount > 0)
        <p class="font-medium text-gray-700 dark:text-gray-300">
            Terpilih: {{ $selectedCount }} transaksi |
            Total Modal: <span class="font-bold">Rp. {{ number_format($totalModal, 0, ',', '.') }}</span> |
            Total Zakat: <span class="font-bold">Rp. {{ number_format($totalZakat, 0, ',', '.') }}</span>
        </p>
    @else
        <p class="text-gray-500 dark:text-gray-400">Pilih transaksi untuk melihat total</p>
    @endif
</div>