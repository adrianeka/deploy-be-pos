<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\App;
use BezhanSalleh\FilamentLanguageSwitch\LanguageSwitch;
use App\Models\PembayaranPembelian;
use App\Models\Pembayaran;
use App\Models\PembayaranPenjualan;
use App\Models\BayarZakat;
use App\Observers\BayarZakatObserver;
use App\Observers\PembayaranObserver;
use App\Observers\PembayaranPembelianObserver;
use App\Observers\PembayaranPenjualanObserver;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // LanguageSwitch::configureUsing(function (LanguageSwitch $switch) {
        //     $switch
        //         ->locales(['id', 'en']);
        // });
        if($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        PembayaranPembelian::observe(PembayaranPembelianObserver::class);
        PembayaranPenjualan::observe(PembayaranPenjualanObserver::class);
        Pembayaran::observe(PembayaranObserver::class);
        BayarZakat::observe(BayarZakatObserver::class);

        App::setLocale('id');
    }
}
