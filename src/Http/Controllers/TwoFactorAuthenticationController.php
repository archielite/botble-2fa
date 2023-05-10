<?php

namespace Botble\TwoFactorAuthentication\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\TwoFactorAuthentication\Actions\DisableTwoFactorAuthentication;
use Botble\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use Botble\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends BaseController
{
    public function store(
        Request $request,
        EnableTwoFactorAuthentication $enable,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $enable($request->user(), $request->input('secret'));

        return $response->setMessage(trans('plugins/2fa::2fa.enable_success'));
    }

    public function destroy(
        ConfirmTwoFactorCodeRequest $request,
        DisableTwoFactorAuthentication $disable,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $disable($request->user(), $request->input('code'));

        return $response->setMessage(trans('plugins/2fa::2fa.disable_success'));
    }
}
