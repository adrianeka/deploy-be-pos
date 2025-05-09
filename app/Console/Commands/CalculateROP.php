<?php

namespace App\Console\Commands;

use App\ReorderPointService;
use Illuminate\Console\Command;

class CalculateROP extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:calculate-r-o-p';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hitung Safety Stock dan Reorder Point setiap awal bulan';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        (new ReorderPointService)->calculate();
        $this->info('Perhitungan ROP selesai.');
    }
}
