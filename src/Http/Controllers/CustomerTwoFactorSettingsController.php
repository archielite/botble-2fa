<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Botble\Ecommerce\Facades\EcommerceHelper;
use Botble\SeoHelper\Facades\SeoHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Http\Response;

class CustomerTwoFactorSettingsController extends BaseCustomerTwoFactorController
{
    public function __construct()
    {
        $this->checkEcommerceCompatibility();

        $version = EcommerceHelper::getAssetVersion();

        Theme::asset()
            ->add('customer-style', 'vendor/core/plugins/ecommerce/css/customer.css', ['bootstrap-css'], version: $version);

        Theme::asset()
            ->add('front-ecommerce-css', 'vendor/core/plugins/ecommerce/css/front-ecommerce.css', version: $version);

        Theme::asset()
            ->container('footer')
            ->add('ecommerce-utilities-js', 'vendor/core/plugins/ecommerce/js/utilities.js', ['jquery'], version: $version)
            ->add('customer-2fa-js', 'vendor/core/plugins/2fa/js/customer-2fa.js', ['jquery'], version: $version);
    }

    public function index(): Response
    {
        SeoHelper::setTitle(trans('plugins/2fa::2fa.name'));

        Theme::breadcrumb()
            ->add(trans('plugins/2fa::2fa.name'), route('customer.two-factor.settings'));

        $customer = auth('customer')->user();
        $hasTwoFactor = TwoFactor::userHasEnabled($customer);

        Theme::asset()
            ->container('footer')
            ->writeContent(
                'customer-2fa-routes',
                sprintf(
                    '<script>window.twoFactorRoutes = %s;</script>',
                    json_encode([
                        'qrCode' => route('customer.two-factor.qr-code'),
                        'confirm' => route('customer.two-factor.confirm'),
                        'recoveryCodes' => route('customer.two-factor.recovery-codes'),
                        'disable' => route('customer.two-factor.disable'),
                    ])
                ),
                ['customer-2fa-js']
            );

        return Theme::scope(
            'ecommerce.customers.two-factor-settings',
            compact('hasTwoFactor'),
            'plugins/2fa::ecommerce.customer-settings'
        )->render();
    }
}
