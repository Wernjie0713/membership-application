<?php

namespace App\Providers;

use App\Models\Member;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
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

        ResetPassword::createUrlUsing(function (User $user, string $token): string {
            return rtrim(config('app.url'), '/').route('password.reset', [
                'token' => $token,
                'email' => $user->email,
            ], false);
        });
    }
}
