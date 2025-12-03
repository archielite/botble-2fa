<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\Ecommerce\Models\Customer;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class CustomerTwoFactorAuthenticatedSessionController extends BaseCustomerTwoFactorController
{
    public function __construct()
    {
        $this->checkEcommerceCompatibility();

        $version = EcommerceHelper::getAssetVersion();

        Theme::asset()
            ->container('footer')
            ->add('ecommerce-utilities-js', 'vendor/core/plugins/ecommerce/js/utilities.js', ['jquery'], version: $version)
            ->add('customer-2fa-challenge-js', 'vendor/core/plugins/2fa/js/customer-2fa-challenge.js', ['jquery'], version: $version);

        $this->middleware(function (Request $request, Closure $closure) {
            if (! session()->has('customer_login.id')) {
                return redirect()->route('customer.login')
                    ->with('error', trans('plugins/2fa::2fa.error_occurred'));
            }

            return $closure($request);
        })->only(['create', 'store']);
    }

    public function create(): Response
    {
        SeoHelper::setTitle(trans('plugins/2fa::2fa.challenge_title'));

        Theme::breadcrumb()
            ->add(trans('plugins/2fa::2fa.challenge_title'), route('customer.two-factor.challenge'));

        return Theme::scope(
            'ecommerce.customers.two-factor-challenge',
            [],
            'plugins/2fa::ecommerce.customer-challenge'
        )->render();
    }

    public function store(
        ConfirmTwoFactorCodeRequest $request,
        ConfirmTwoFactorAuthentication $confirm
    ): BaseHttpResponse {
        $customer = Customer::query()->findOrFail($request->session()->get('customer_login.id'));

        $confirm($customer, $request->input('code') ?: $request->input('recovery_code'));

        session()->put('customer_2fa_authenticated', true);

        Auth::guard('customer')->login($customer, $request->session()->get('customer_login.remember', false));

        session()->forget(['customer_login.id', 'customer_login.remember']);

        return $this
            ->httpResponse()
            ->setData([
                'next_url' => route('customer.overview'),
            ]);
    }
}
