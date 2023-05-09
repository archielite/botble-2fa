<?php

namespace Botble\TwoFa\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\TwoFa\Actions\ConfirmTwoFactorAuthentication;
use Botble\TwoFa\Http\Requests\ConfirmTwoFactorCodeRequest;

class ConfirmedTwoFactorAuthenticationController extends BaseController
{
    public function store(
        ConfirmTwoFactorCodeRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $confirm($request->user(), $request->input('code'));

        return $response->setMessage(__('You have successfully enabled two-factor authentication.'));
    }
}
