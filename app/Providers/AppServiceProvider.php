<?php

namespace App\Providers;

use App\Services\GeneralSettings;
use App\Support\IndianPhoneNumber;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(GeneralSettings::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view): void {
            $view->with('generalSettings', app(GeneralSettings::class)->get());
        });

        RateLimiter::for('login', function (Request $request): Limit {
            $login = trim((string) $request->input('login', ''));
            $login = filter_var($login, FILTER_VALIDATE_EMAIL)
                ? Str::lower($login)
                : IndianPhoneNumber::normalize($login);

            return Limit::perMinute(5)->by($request->ip().'|'.$login);
        });

        RateLimiter::for('register', fn (Request $request): Limit =>
            Limit::perMinute(5)->by($request->ip())
        );
    }
}
