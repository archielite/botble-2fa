<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Illuminate\Http\Request;

class RecoveryCodeController extends BaseController
{
    public function index(Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('user_id', $request->user()->id)
            ->first();

        if (! $twoFactor->secret || ! $twoFactor->recovery_codes) {
            return $response;
        }

        return $response
            ->setData([
                'recovery_codes' => json_decode(decrypt(
                    $twoFactor->recovery_codes
                ), true),
            ]);
    }
}
