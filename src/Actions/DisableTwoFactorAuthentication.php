<?php

namespace Botble\TwoFa\Actions;

use Botble\ACL\Models\User;
use Botble\TwoFa\Models\TwoFactorAuthentication;

class DisableTwoFactorAuthentication
{
    public function __invoke(User $user): void
    {
        $twoFactor = TwoFactorAuthentication::query()->where('user_id', $user->id)->first();

        if (! empty($twoFactor->secret)
            || ! empty($twoFactor->recovery_codes)
        ) {
            $twoFactor->delete();
        }
    }
}
