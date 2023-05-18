<?php

namespace ArchiElite\TwoFactorAuthentication\Providers;

use ArchiElite\TwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Illuminate\Cache\Repository;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;

    public function register(): void
    {
        if (! class_exists('PragmaRX\Google2FA\Google2FA')) {
            require __DIR__ . '/../../vendor/autoload.php';
        }

        $this->app->singleton(TwoFactorAuthenticationProviderContract::class, function ($app) {
            return new TwoFactorAuthenticationProvider(
                $app->make(Google2FA::class),
                $app->make(Repository::class)
            );
        });
    }

    public function boot(): void
    {
        $this
            ->setNamespace('plugins/2fa')
            ->loadAndPublishTranslations()
            ->loadAndPublishViews()
            ->loadMigrations()
            ->publishAssets()
            ->loadRoutes();

        $this->app->booted(function () {
            $this->app->register(HookServiceProvider::class);
        });
    }
}
