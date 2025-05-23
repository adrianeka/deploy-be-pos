<?php

use App\Console\Commands\CalculateROP;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    /** @var ClosureCommand $this */
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule::command('app:calculate-r-o-p')->monthlyOn(1, '01:00');
// Schedule::command('app:calculate-r-o-p')->everyMinute();
Schedule::command('app:calculate-r-o-p')->everyThreeMinutes();