<?php

namespace Botble\TwoFa\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\TwoFa\Actions\DisableTwoFactorAuthentication;
use Botble\TwoFa\Actions\EnableTwoFactorAuthentication;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends BaseController
{
    public function store(
        Request $request,
        EnableTwoFactorAuthentication $enable,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $enable($request->user());

        return $response->setMessage(trans('plugins/2fa::2fa.enable_success'));
    }

    public function destroy(
        Request $request,
        DisableTwoFactorAuthentication $disable,
        BaseHttpResponse $response
    ): BaseHttpResponse {
        $disable($request->user());

        return $response->setMessage(trans('plugins/2fa::2fa.disable_success'));
    }
}
