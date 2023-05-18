<?php

namespace ArchiElite\TwoFactorAuthentication\Providers;

use Botble\Base\Facades\Assets;
use ArchiElite\TwoFactorAuthentication\Actions\RedirectIfTwoFactorAuthenticatable;
use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, function (string $data): string {
            return $data . view('plugins/2fa::settings')->render();
        }, 999);

        if (! TwoFactor::isSettingEnabled()) {
            return;
        }

        add_filter(ACL_FILTER_PROFILE_FORM_TABS, function (string $data) {
            return $data . view('plugins/2fa::profile.tab')->render();
        });

        add_filter(ACL_FILTER_PROFILE_FORM_TAB_CONTENTS, function (string $data) {
            Assets::usingVueJS()
                ->addScriptsDirectly('vendor/core/plugins/2fa/js/2fa.js');

            return $data . view('plugins/2fa::profile.content')->render();
        });

        add_filter('core_acl_login_pipeline', function (array $pipeline): array {
            if (! TwoFactor::isSettingEnabled()) {
                return $pipeline;
            }

            return array_merge([
                RedirectIfTwoFactorAuthenticatable::class,
            ], $pipeline);
        }, 999);
    }
}
