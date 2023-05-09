<?php

namespace Botble\TwoFa\Actions;

use Botble\ACL\Models\User;
use Botble\TwoFa\Contracts\TwoFactorAuthenticationProvider;
use Botble\TwoFa\Models\TwoFactorAuthentication;
use Botble\TwoFa\RecoveryCode;
use Illuminate\Support\Collection;

class CreateTwoFactorRecord
{
    public function __invoke(User $user): string
    {
        $recoveryCodes = encrypt(json_encode(Collection::times(8, function () {
            return RecoveryCode::generate();
        })->all()));

        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'recovery_codes' => $recoveryCodes,
        ]);

        return encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey());
    }
}
