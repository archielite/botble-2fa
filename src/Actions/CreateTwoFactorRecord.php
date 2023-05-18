<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use Botble\ACL\Models\User;
use ArchiElite\TwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Illuminate\Support\Collection;

class CreateTwoFactorRecord
{
    public function __invoke(User $user): string
    {
        $recoveryCodes = encrypt(
            json_encode(
                Collection::times(8, function () {
                    return RecoveryCode::generate();
                })->all()
            )
        );

        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'recovery_codes' => $recoveryCodes,
        ]);

        return encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey());
    }
}
