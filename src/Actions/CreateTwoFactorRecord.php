<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Contracts\TwoFactorAuthenticationProvider;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class CreateTwoFactorRecord
{
    public function __invoke(Authenticatable $user): string
    {
        $recoveryCodes = Crypt::encrypt(
            json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())
        );

        TwoFactorAuthentication::query()->updateOrCreate([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
        ], [
            'recovery_codes' => $recoveryCodes,
        ]);

        return Crypt::encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey());
    }
}
