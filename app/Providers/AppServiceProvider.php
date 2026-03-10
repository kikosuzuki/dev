<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

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
        $appUrl = config('app.url');

        if ($appUrl) {
            URL::forceRootUrl($appUrl);
        }

        if (str_starts_with($appUrl, 'https')) {
            URL::forceScheme('https');
        }

        // サブディレクトリ配置時にセッションCookieのパスを自動設定
        $path = parse_url($appUrl, PHP_URL_PATH);
        if ($path && $path !== '/') {
            config(['session.path' => $path]);
        }
    }
}
