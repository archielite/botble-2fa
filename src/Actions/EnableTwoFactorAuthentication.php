<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class EnableTwoFactorAuthentication
{
    public function __invoke(Authenticatable $user, string $secret): void
    {
        TwoFactorAuthentication::query()->updateOrCreate([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
        ], [
            'secret' => Crypt::encrypt($secret),
            'recovery_codes' => Crypt::encrypt(
                json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())
            ),
        ]);
    }
}
