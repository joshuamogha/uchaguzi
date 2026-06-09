<?php

namespace App\Providers;

use App\Models\Election;
use App\Models\User;
use App\Policies\ElectionPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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
        Gate::policy(Election::class, ElectionPolicy::class);
        Gate::define('admin-only', fn (User $user) => $user->isAdmin());
        Paginator::useBootstrapFive();

        RateLimiter::for('vote-verification', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for('vote-pin', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().':'.$request->input('token'));
        });
    }
}
