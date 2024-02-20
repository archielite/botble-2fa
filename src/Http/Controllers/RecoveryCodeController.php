<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class RecoveryCodeController extends BaseController
{
    public function index(Request $request): BaseHttpResponse
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('user_id', $request->user()->getKey())
            ->first();

        if (! $twoFactor->secret || ! $twoFactor->recovery_codes) {
            return $this->httpResponse();
        }

        return $this
            ->httpResponse()
            ->setData([
                'recovery_codes' => json_decode(Crypt::decrypt(
                    $twoFactor->recovery_codes
                ), true),
            ]);
    }
}
