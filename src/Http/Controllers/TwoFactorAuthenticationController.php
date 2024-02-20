<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Actions\DisableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class TwoFactorAuthenticationController extends BaseController
{
    public function store(
        Request $request,
        EnableTwoFactorAuthentication $enable,
    ): BaseHttpResponse {
        $enable($request->user(), $request->input('secret'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/2fa::2fa.enable_success'));
    }

    public function destroy(
        ConfirmTwoFactorCodeRequest $request,
        DisableTwoFactorAuthentication $disable,
    ): BaseHttpResponse {
        $disable($request->user(), $request->input('code'));

        return $this
            ->httpResponse()
            ->setMessage(trans('plugins/2fa::2fa.disable_success'));
    }
}
