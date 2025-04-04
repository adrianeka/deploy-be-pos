<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

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
        Request::setTrustedProxies(['REMOTE_ADDR'], SymfonyRequest::HEADER_X_FORWARDED_FOR);
    }
}
