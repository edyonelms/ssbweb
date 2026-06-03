<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Resolve the active logo URL once per request and share it with
        // every view so any branding change applied via the Profile page
        // reflects across the app immediately.
        $logoUrl = null;
        View::composer('*', function ($view) use (&$logoUrl) {
            if ($logoUrl === null) {
                try {
                    $logoUrl = Schema::hasTable('settings')
                        ? Settings::current()->logo_url
                        : asset('images/logo.png');
                } catch (\Throwable $e) {
                    $logoUrl = asset('images/logo.png');
                }
            }
            $view->with('logoUrl', $logoUrl);
        });
    }
}
