<?php

namespace App\Providers;

use App\Models\Member;
use Illuminate\Support\Facades\Route;
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
        Route::bind('member', fn (string $value) => Member::withTrashed()->findOrFail($value));
    }
}
