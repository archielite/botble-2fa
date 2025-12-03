<?php

namespace ArchiElite\TwoFactorAuthentication\Providers;

use ArchiElite\TwoFactorAuthentication\Actions\RedirectIfTwoFactorAuthenticatable;
use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Botble\Base\Facades\Assets;
use Botble\Base\Facades\DashboardMenu;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ServiceProvider;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! TwoFactor::isSettingEnabled()) {
            return;
        }

        add_filter(ACL_FILTER_PROFILE_FORM_TABS, function (string $data) {
            if (self::shouldShowInProfile()) {
                $data .= Blade::render(
                    sprintf('<x-core::tab.item id="twofa" label="%s"  icon="ti ti-lock" />', trans('plugins/2fa::2fa.name'))
                );
            }

            return $data;
        });

        add_filter(ACL_FILTER_PROFILE_FORM_TAB_CONTENTS, function (string $data) {
            if (self::shouldShowInProfile()) {
                $js = version_compare(get_core_version(), '6.7.2', '>') ? '2fa-vue3.js' : '2fa.js';

                Assets::usingVueJS()->addScriptsDirectly('vendor/core/plugins/2fa/js/' . $js);

                $data .= view('plugins/2fa::profile.content')->render();
            }

            return $data;
        });

        add_filter('core_acl_login_pipeline', function (array $pipeline): array {
            if (! TwoFactor::isSettingEnabled()) {
                return $pipeline;
            }

            return array_merge([
                RedirectIfTwoFactorAuthenticatable::class,
            ], $pipeline);
        }, 999);

        if (is_plugin_active('ecommerce') && version_compare(EcommerceHelper::getAssetVersion(), '3.11.3', '>=')) {
            add_filter('customer_login_response', function ($response, $customer, $request) {
                if (! TwoFactor::isSettingEnabled()) {
                    return $response;
                }

                if (session()->has('customer_2fa_authenticated')) {
                    session()->forget('customer_2fa_authenticated');

                    return $response;
                }

                if (TwoFactor::userHasEnabled($customer)) {
                    session()->put('customer_login.id', $customer->getKey());
                    session()->put('customer_login.remember', $request->filled('remember'));

                    Auth::guard('customer')->logout();

                    return redirect()->route('customer.two-factor.challenge');
                }

                return $response;
            }, 10, 3);

            DashboardMenu::for('customer')->beforeRetrieving(function (): void {
                if (! TwoFactor::isSettingEnabled()) {
                    return;
                }

                DashboardMenu::make()->registerItem([
                    'id' => 'cms-customer-two-factor',
                    'priority' => 65,
                    'name' => trans('plugins/2fa::2fa.name'),
                    'url' => fn () => route('customer.two-factor.settings'),
                    'icon' => 'ti ti-shield-lock',
                ]);
            });
        }
    }

    protected static function shouldShowInProfile(): bool
    {
        return Request::route('user')->is(Auth::user());
    }
}
