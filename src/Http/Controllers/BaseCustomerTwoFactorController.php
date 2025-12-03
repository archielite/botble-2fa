<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Ecommerce\Facades\EcommerceHelper;

abstract class BaseCustomerTwoFactorController extends BaseController
{
    protected function checkEcommerceCompatibility(): void
    {
        if (! is_plugin_active('ecommerce')) {
            abort(404);
        }

        if (version_compare(EcommerceHelper::getAssetVersion(), '3.11.3', '<')) {
            abort(404);
        }
    }
}
