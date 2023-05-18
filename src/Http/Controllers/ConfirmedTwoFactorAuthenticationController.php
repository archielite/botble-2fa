<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use ArchiElite\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;

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
