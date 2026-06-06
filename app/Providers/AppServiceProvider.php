<?php

namespace App\Providers;

use App\Models\Settings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
        //
        // Brand assets live on the EC2 server's persistent storage disk
        // (storage/app/public/branding/...) when present; if a developer
        // ever removes the bundled copy from public/images/ locally, the
        // server copy still keeps the portal looking right.
        $brandAssets = Cache::rememberForever('brand:data-uris:v2', function () {
            $resolve = function (string $key, string $bundledRel, string $mime) {
                foreach (['png', 'jpg', 'jpeg', 'webp', 'svg'] as $ext) {
                    $rel = "branding/{$key}.{$ext}";
                    if (Storage::disk('public')->exists($rel)) {
                        $bytes = Storage::disk('public')->get($rel);
                        $extMime = $ext === 'jpg' ? 'jpeg' : $ext;
                        return 'data:image/'.$extMime.';base64,'.base64_encode($bytes);
                    }
                }
                $path = public_path($bundledRel);
                return is_file($path)
                    ? 'data:'.$mime.';base64,'.base64_encode(file_get_contents($path))
                    : asset($bundledRel);
            };
            return [
                'logo'      => $resolve('logo',       'images/logo.png',       'image/png'),
                'loginLeft' => $resolve('login-left', 'images/login-left.png', 'image/png'),
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
