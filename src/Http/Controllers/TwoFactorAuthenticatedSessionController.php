<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Botble\ACL\Models\User;
use Botble\Base\Facades\Assets;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TwoFactorAuthenticatedSessionController extends BaseController
{
    public function __construct()
    {
        $this->middleware(function (Request $request, Closure $closure) {
            if (! session()->has(['login.id'])) {
                return redirect()->route('access.login');
            }

            return $closure($request);
        });
    }

    public function create(): View
    {
        Assets::usingVueJS()
            ->addStylesDirectly('vendor/core/core/acl/css/animate.min.css')
            ->addStylesDirectly('vendor/core/core/acl/css/login.css')
            ->addScriptsDirectly('vendor/core/plugins/2fa/js/2fa-vue3.js')
            ->removeStyles([
                'select2',
                'fancybox',
                'spectrum',
                'simple-line-icons',
                'custom-scrollbar',
                'datepicker',
            ])
            ->removeScripts([
                'select2',
                'fancybox',
                'cookie',
            ]);

        return view('plugins/2fa::challenge');
    }

    public function store(
        ConfirmTwoFactorCodeRequest $request,
        BaseHttpResponse $response,
        ConfirmTwoFactorAuthentication $confirm
    ): BaseHttpResponse {
        $user = User::query()->findOrFail($request->session()->get('login.id'));

        $confirm($user, $request->input('code') ?? $request->input('recovery_code'));

        Auth::login($user, $request->session()->get('login.remember', false));

        $user->update(['last_login' => Carbon::now()]);

        session()->forget(['login.id', 'login.remember']);

        return $response
            ->setData([
                'next_url' => route('dashboard.index'),
            ]);
    }
}
