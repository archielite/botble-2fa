<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Illuminate\Http\Request;

class RecoveryCodeController extends BaseController
{
    public function index(Request $request): BaseHttpResponse
    {
        $user = $request->user();

        // The table is polymorphic since 2025_12_03_073019_convert_two_factor_to_polymorphic (user_id was renamed).
        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $user->getKey())
            ->where('authenticatable_type', get_class($user))
            ->first();

        if (! $twoFactor?->secret || ! $twoFactor->recovery_codes) {
            return $this->httpResponse();
        }

        return $this
            ->httpResponse()
            ->setData([
                'recovery_codes' => $twoFactor->recoveryCodes(),
            ]);
    }
}
