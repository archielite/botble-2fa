<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\DisableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class CustomerTwoFactorAuthenticationController extends BaseCustomerTwoFactorController
{
    public function store(
        Request $request,
        EnableTwoFactorAuthentication $enable,
    ): BaseHttpResponse {
        $customer = auth('customer')->user();
        $enable($customer, $request->input('secret'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/2fa::2fa.enable_success'));
    }

    public function destroy(
        ConfirmTwoFactorCodeRequest $request,
        DisableTwoFactorAuthentication $disable,
    ): BaseHttpResponse {
        $customer = auth('customer')->user();
        $disable($customer, $request->input('code'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/2fa::2fa.disable_success'));
    }
}
