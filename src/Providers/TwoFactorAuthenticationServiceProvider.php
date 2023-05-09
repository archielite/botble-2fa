<?php

namespace Botble\TwoFa\Providers;

use Botble\ACL\Traits\AuthenticatesUsers;
use Botble\Base\Facades\Assets;
use Botble\Base\Traits\LoadAndPublishDataTrait;
use Botble\TwoFa\Contracts\TwoFactorAuthenticationProvider as TwoFactorAuthenticationProviderContract;
use Botble\TwoFa\TwoFa;
use Botble\TwoFa\TwoFactorAuthenticationProvider;
use Closure;
use Illuminate\Cache\Repository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorAuthenticationServiceProvider extends ServiceProvider
{
    use LoadAndPublishDataTrait;
    use AuthenticatesUsers;

    public function register(): void
    {
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
            add_filter(BASE_FILTER_AFTER_SETTING_CONTENT, function (string $data): string {
                return $data . view('plugins/2fa::settings')->render();
            }, 999);

            add_filter(ACL_FILTER_PROFILE_FORM_TABS, function (string $data) {
                return $data . view('plugins/2fa::profile.tab')->render();
            });

            add_filter(ACL_FILTER_PROFILE_FORM_TAB_CONTENTS, function (string $data) {
                Assets::usingVueJS()
                    ->addScriptsDirectly('vendor/core/plugins/2fa/js/2fa.js');

                return $data . view('plugins/2fa::profile.content')->render();
            });

            add_filter('core_acl_login_pipeline', function (array $pipeline): array {
                if (! TwoFa::isSettingEnabled()) {
                    return $pipeline;
                }

                return [
                    function (Request $request, Closure $next) {
                        if (! Auth::once($request->only(['email', 'password']))) {
                            $this->incrementLoginAttempts($request);

                            return $this->sendFailedLoginResponse();
                        }

                        $user = Auth::user();

                        if (! TwoFa::userHasEnabled($user)) {
                            return $next($request);
                        }

                        session()->put('login.id', $user->id);
                        session()->put('login.remember', $request->has('remember'));

                        return redirect()->route('two-factor.challenge');
                    },
                ] + $pipeline;
            }, 999);
        });
    }
}
