<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Botble\Base\Http\Responses\BaseHttpResponse;

class CustomerRecoveryCodeController extends BaseCustomerTwoFactorController
{
    public function index(): BaseHttpResponse
    {
        $customer = auth('customer')->user();
        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $customer->getKey())
            ->where('authenticatable_type', get_class($customer))
            ->first();

        return $this
            ->httpResponse()
            ->setData([
                'recovery_codes' => $twoFactor ? $twoFactor->recoveryCodes() : [],
            ]);
    }
}
