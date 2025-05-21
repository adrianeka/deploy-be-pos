<?php

namespace App\Filament\Widgets;

use App\Models\Pembelian;
use App\Models\Penjualan;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Facades\Log;

class PembelianChart extends ChartWidget
{
    protected static ?string $heading = 'Pengeluaran vs Utang';
    protected static ?string $pollingInterval = '10s';
    protected static ?string $maxHeight = '300px';

    public ?string $filter = 'month'; // Default value
    public ?string $filterPeriod = 'month';
    public ?string $startDate = null;
    public ?string $endDate = null;
    public bool $showUtang = true;

    public function mount(): void
    {
        // Inisialisasi nilai default
        $this->applyQuickFilter($this->filter);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('filter')
                    ->options($this->getFilters())
                    ->label('Filter Cepat')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->filter = $state;
                        $this->applyQuickFilter($state);
                    }),
                    
                Select::make('filterPeriod')
                    ->options([
                        'day' => 'Harian',
                        'month' => 'Bulanan',
                        'year' => 'Tahunan',
                    ])
                    ->default('month')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->filterPeriod = $state;
                        $this->filter = null; // Reset quick filter
                    })
                    ->label('Periode'),

                DatePicker::make('startDate')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->startDate = $state;
                        $this->filter = null; // Reset quick filter
                    })
                    ->label('Dari Tanggal'),

                DatePicker::make('endDate')
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->endDate = $state;
                        $this->filter = null; // Reset quick filter
                    })
                    ->label('Sampai Tanggal'),

                Toggle::make('showUtang')
                    ->label('Tampilkan Piutang')
                    ->default(true)
                    ->reactive()
                    ->afterStateUpdated(function ($state) {
                        $this->showUtang = $state;
                    }),
            ])
            ->columns(4);
    }

    protected function applyQuickFilter(string $filter): void
    {
        $now = Carbon::now();
        
        switch ($filter) {
            case 'today':
                $this->startDate = $now->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                $this->filterPeriod = 'day';
                break;
                
            case 'week':
                $this->startDate = $now->copy()->subWeek()->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                $this->filterPeriod = 'day';
                break;
                
            case 'month':
                $this->startDate = $now->copy()->subMonth()->startOfDay()->toDateString();
                $this->endDate = $now->endOfDay()->toDateString();
                $this->filterPeriod = 'day'; // Diubah dari 'day' ke 'month'
                break;
                
            case 'year':
                $this->startDate = $now->copy()->subYear()->startOfDay()->toDateString(); // 1 tahun ke belakang dari sekarang
                $this->endDate = $now->endOfDay()->toDateString();
                $this->filterPeriod = 'month';
                break;
        }
        
        Log::info('Quick filter applied', [
            'filter' => $filter,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'period' => $this->filterPeriod
        ]);
    }

    protected function getData(): array
    {
        // Jika filter aktif, gunakan nilai dari filter
        if ($this->filter) {
            $this->applyQuickFilter($this->filter);
        }
        
        // Default values jika null
        $startDate = $this->startDate 
            ? Carbon::parse($this->startDate)->startOfDay() 
            : now()->subMonth()->startOfDay();
            
        $endDate = $this->endDate 
            ? Carbon::parse($this->endDate)->endOfDay() 
            : now()->endOfDay();

        $periodMethod = 'per' . ucfirst($this->filterPeriod);

        $pengeluaranTrend = Trend::model(Pembelian::class)
            ->between($startDate, $endDate)
            ->{$periodMethod}()
            ->count();
        
        $dates = $pengeluaranTrend;
        $pengeluaranData = collect($dates)->map(function ($item) use ($periodMethod) {
            if ($periodMethod == 'perDay') {
                $startDate = Carbon::parse($item->date)->startOfDay();
                $endDate = Carbon::parse($item->date)->endOfDay();
            } elseif ($periodMethod == 'perMonth') {
                $startDate = Carbon::parse($item->date)->startOfMonth();
                $endDate = Carbon::parse($item->date)->endOfMonth();
            } elseif ($periodMethod == 'perYear') {
                $startDate = Carbon::parse($item->date)->subYear()->startOfDay();
                $endDate = Carbon::parse($item->date)->endOfDay();
            }

            $pembelians = Pembelian::whereBetween('created_at', [$startDate, $endDate])->get();   
            $dibayar = $pembelians->sum('uangBayar');
            $kembalian = $pembelians->sum('uangKembalian');
            $totalPengeluaran = $dibayar - $kembalian;
            
            return new TrendValue($item->date, $totalPengeluaran, $totalPengeluaran);
        });

        $utangData = null;
        if ($this->showUtang) {
            $piutangTrend = Trend::query(
                Pembelian::whereIn('status_pembelian', ['belum lunas', 'pesanan'])
            )
                ->between($startDate, $endDate)
                ->{$periodMethod}();
                
            $dates = $piutangTrend->count(); // Ambil tanggal saja
            $utangData = collect($dates)->map(function ($item) use ($periodMethod) {
                if ($periodMethod == 'perDay') {
                    $startDate = Carbon::parse($item->date)->startOfDay();
                    $endDate = Carbon::parse($item->date)->endOfDay();
                } elseif ($periodMethod == 'perMonth') {
                    $startDate = Carbon::parse($item->date)->startOfMonth();
                    $endDate = Carbon::parse($item->date)->endOfMonth();
                } elseif ($periodMethod == 'perYear') {
                    $startDate = Carbon::parse($item->date)->subYear()->startOfDay();
                    $endDate = Carbon::parse($item->date)->endOfDay();
                }
            $pembelians = Pembelian::whereIn('status_pembelian', ['belum lunas', 'pesanan'])
                ->whereBetween('created_at', [$startDate, $endDate])
                ->get();
                
            $totalPiutang = $pembelians->sum('sisaPembayaran');
            return new TrendValue($item->date, $totalPiutang, $totalPiutang);
            });
        } else {
            $utangData = collect();
        }
        // Format labels
        $labels = $pengeluaranData->map(function (TrendValue $value) {
            $date = Carbon::parse($value->date);
            
            return match ($this->filterPeriod) {
                'day' => $date->translatedFormat('d M'),
                'month' => $date->translatedFormat('M Y'),
                'year' => $date->translatedFormat('Y'),
                default => $date->translatedFormat('d M Y'),
            };
        })->values()->all();

        // Format datasets
        $datasets = [
            [
                'label' => 'Total Pengeluaran',
                'data' => $pengeluaranData->map(fn (TrendValue $value) => $value->aggregate)->all(),
                'borderColor' => '#10B981',
                'backgroundColor' => 'rgba(16, 185, 129, 0.2)',
                'tension' => 0.4,
                'fill' => true,
            ]
        ];

        if ($this->showUtang) {
            $datasets[] = [
                'label' => 'Total Piutang',
                'data' => $utangData->map(fn (TrendValue $value) => $value->aggregate)->all(),
                'borderColor' => '#EF4444',
                'backgroundColor' => 'rgba(239, 68, 68, 0.2)',
                'tension' => 0.4,
                'fill' => true,
            ];
        }
        // $pembelians = Pembelian::get();
        // $pengeluaran = $pembelians->sum('uangBayar');
        // $kembalian = $pembelians->sum('uangKembalian');
        // $piutang = $pembelians->sum('sisaPembayaran');
        // Log::info("=== COMPARE DARI DB LANGSUNG ===");
        // Log::info("Pengeluaran Langsung: Rp " . number_format($pengeluaran - $kembalian, 0, ',', '.'));
        // Log::info("Utang Langsung: Rp " . number_format($piutang, 0, ',', '.'));

        // Log::info("=== DARI TREND ===");
        // Log::info("Total Pengeluaran dari Trend: Rp " . number_format($pengeluaranData->sum('aggregate'), 0, ',', '.'));
        // Log::info("Total Utang dari Trend: Rp " . number_format($utangData->sum('aggregate'), 0, ',', '.'));
        // Log::info("datasets", $datasets);
        // Log::info("labels", $labels);
        return [
            'datasets' => $datasets,
            'labels' => $labels,
        ];
    }


    protected function getFilters(): ?array
    {
        return [
            'today' => 'Hari ini',
            'week' => 'Minggu terakhir',
            'month' => 'Bulan terakhir',
            'year' => 'Tahun ini',
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
