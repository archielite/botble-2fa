<?php

namespace ArchiElite\TwoFactorAuthentication\Providers;

use ArchiElite\TwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\Base\Facades\PanelSectionManager;
use Botble\Base\PanelSections\PanelSectionItem;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\Setting\PanelSections\SettingOthersPanelSection;
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

        PanelSectionManager::default()->beforeRendering(function () {
            PanelSectionManager::registerItem(
                SettingOthersPanelSection::class,
                fn () => PanelSectionItem::make('two-factor-authentication')
                    ->setTitle(trans('plugins/2fa::2fa.settings.title'))
                    ->withIcon('ti ti-lock')
                    ->withDescription(trans('plugins/2fa::2fa.settings.description'))
                    ->withPriority(980)
                    ->withPermission('two-factor-authentication.settings')
                    ->withRoute('two-factor.settings')
            );
        });
    }
}
