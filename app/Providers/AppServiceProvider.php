<?php

namespace App\Providers;

use App\Models\AdminMenuItem;
use App\Models\LeiBusinessSetting;
use App\Models\LeiStaticPage;
use App\Support\CurrencyFormatter;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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
        if ($rootUrl = config('app.url')) {
            $configuredUrl = rtrim($rootUrl, '/');

            if ($this->app->environment('local') && ! $this->app->runningInConsole()) {
                $request = request();
                if ($request && $request->getHttpHost()) {
                    $rootUrl = $request->getSchemeAndHttpHost();
                } else {
                    $rootUrl = $configuredUrl;
                }
            } else {
                $rootUrl = $configuredUrl;
            }

            URL::forceRootUrl($rootUrl);

            $basePath = parse_url($configuredUrl, PHP_URL_PATH) ?: '';
            if ($basePath && $basePath !== '/') {
                Paginator::currentPathResolver(function () use ($basePath) {
                    return rtrim($basePath, '/').'/'.ltrim(request()->path(), '/');
                });
            }
        }

        $shareBusiness = function ($view) {
            try {
                $businessSettings = LeiBusinessSetting::current();
            } catch (\Throwable) {
                $businessSettings = new LeiBusinessSetting(LeiBusinessSetting::defaults());
            }
            $view->with('businessSettings', $businessSettings);
            CurrencyFormatter::applyLocale();
        };

        View::composer(['admin.layouts.app', 'admin.auth.login'], function ($view) use ($shareBusiness) {
            $shareBusiness($view);
        });

        View::composer(['public.layouts.app', 'public.*'], function ($view) use ($shareBusiness) {
            $shareBusiness($view);
            try {
                $view->with('footerPages', LeiStaticPage::query()
                    ->where('status', 'published')
                    ->where('is_in_footer', true)
                    ->orderBy('sort_order')
                    ->get());
            } catch (\Throwable) {
                $view->with('footerPages', collect());
            }
        });

        View::composer(['applicant.layouts.app', 'applicant.*'], function ($view) use ($shareBusiness) {
            $shareBusiness($view);
        });

        View::composer('admin.layouts.app', function ($view) {
            $view->with('menuItems', AdminMenuItem::where('is_active', true)->orderBy('sort_order')->get());
        });

        View::composer('admin.*', $shareBusiness);
    }
}
