<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Crypt;

class DisableTwoFactorAuthentication
{
    public function __construct(protected ConfirmTwoFactorAuthentication $confirm)
    {
    }

    public function __invoke(Authenticatable $user, string $code): void
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $user->getKey())
            ->where('authenticatable_type', get_class($user))
            ->first();

        if (
            ! empty($twoFactor->secret)
            || ! empty($twoFactor->recovery_codes)
        ) {
            $this->confirm->__invoke($user, $code, Crypt::decrypt($twoFactor->secret));

            $twoFactor->delete();
        }
    }
}
