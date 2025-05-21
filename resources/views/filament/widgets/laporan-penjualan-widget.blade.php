@php
    $stats = $this->getStatsData();
    $heading = $this->getHeading();
    $chart = $this->getChartData();
@endphp

<!-- Judul -->
@if ($heading)
    <div class="px-4 pb-4">
        <h2 class="text-lg font-medium tracking-tight text-gray-900 dark:text-white">
            {{ $heading }}
        </h2>
    </div>
@endif

<!-- Statistik (3 kolom) -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4 px-4 pb-4">
    @foreach ($stats as $stat)
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                    {{ $stat->getLabel() }}
                </p>
                @if ($stat->getDescriptionIcon())
                    <x-dynamic-component :component="$stat->getDescriptionIcon()" class="h-5 w-5 text-gray-400" />
                @endif
            </div>
            <p class="mt-1 text-2xl font-semibold text-gray-800 dark:text-white">
                {{ $stat->getValue() }}
            </p>
            @if ($stat->getDescription())
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ $stat->getDescription() }}
                </p>
            @endif
        </div>
    @endforeach
</div>

<!-- Chart -->
<div 
    id="{{ $this->getId() }}" 
    data-chart="{{ json_encode(['datasets' => $chart['datasets'], 'labels' => $chart['labels']], JSON_UNESCAPED_UNICODE) }}" 
    class="filament-chart-widget px-4 pb-4" 
    style="min-height: 320px;"
>
    <canvas></canvas>
</div>
