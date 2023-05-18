<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use Botble\ACL\Models\User;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;

class DisableTwoFactorAuthentication
{
    public function __construct(protected ConfirmTwoFactorAuthentication $confirm)
    {
    }

    public function __invoke(User $user, string $code): void
    {
        $twoFactor = TwoFactorAuthentication::query()->where('user_id', $user->id)->first();

        if (! empty($twoFactor->secret)
            || ! empty($twoFactor->recovery_codes)
        ) {
            $this->confirm->__invoke($user, $code, decrypt($twoFactor->secret));

            $twoFactor->delete();
        }
    }
}
