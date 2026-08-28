<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Gate::define('access-admin', fn (User $user) => $user->role === UserRole::SuperAdmin);
        Gate::define('access-exam-panel', fn (User $user) => in_array(
            $user->role,
            [UserRole::SuperAdmin, UserRole::Panitia],
            true
        ));

        View::composer('*', function ($view): void {
            $settings = Cache::remember('app-settings.current', now()->addMinutes(5), function (): AppSetting {
                if (! Schema::hasTable('app_settings')) {
                    return new AppSetting;
                }

                return AppSetting::query()->first() ?? new AppSetting;
            });

            $view->with('appSettings', $settings);
        });
    }
}
