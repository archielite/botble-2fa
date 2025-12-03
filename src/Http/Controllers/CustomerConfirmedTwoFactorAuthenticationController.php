<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Botble\Base\Http\Responses\BaseHttpResponse;

class CustomerConfirmedTwoFactorAuthenticationController extends BaseCustomerTwoFactorController
{
    public function store(
        ConfirmTwoFactorCodeRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
    ): BaseHttpResponse {
        $customer = auth('customer')->user();
        $secret = $request->input('secret');

        $confirm($customer, $request->input('code'), $secret);

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/2fa::2fa.confirm_success'));
    }
}
