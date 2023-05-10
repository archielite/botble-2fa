<?php

namespace Botble\TwoFactorAuthentication\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use Botble\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;

class ConfirmedTwoFactorAuthenticationController extends BaseController
{
    public function store(
        ConfirmTwoFactorCodeRequest $request,
        ConfirmTwoFactorAuthentication $confirm,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $confirm($request->user(), $request->input('code'), $request->input('secret'));

        return $response;
    }
}
