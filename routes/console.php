<?php

use App\Console\Commands\CalculateROP;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command(CalculateROP::class)
//     ->monthlyOn(1, '00:00');