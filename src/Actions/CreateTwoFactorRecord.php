<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Botble\ACL\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class CreateTwoFactorRecord
{
    public function __invoke(User $user): string
    {
        $recoveryCodes = Crypt::encrypt(
            json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())
        );

        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'recovery_codes' => $recoveryCodes,
        ]);

        return Crypt::encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey());
    }
}
