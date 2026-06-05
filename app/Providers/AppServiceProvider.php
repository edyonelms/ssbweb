<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
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
        // Inline the bundled brand PNGs as data URIs so they ship with the
        // HTML and require no extra round-trip — they appear with the very
        // first paint instead of a network spinner.
        $brandAssets = Cache::rememberForever('brand:data-uris', function () {
            $encode = function (string $rel, string $mime) {
                $path = public_path($rel);
                return is_file($path)
                    ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path))
                    : asset($rel);
            };
            return [
                'logo'      => $encode('images/logo.png', 'image/png'),
                'loginLeft' => $encode('images/login-left.png', 'image/png'),
            ];
        });

        View::composer('*', function ($view) use (&$logoUrl, $brandAssets) {
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
            $view->with('logoDataUri', $brandAssets['logo']);
            $view->with('loginLeftDataUri', $brandAssets['loginLeft']);
        });
    }
}
