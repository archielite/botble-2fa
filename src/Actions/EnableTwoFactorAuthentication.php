<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Botble\ACL\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Crypt;

class EnableTwoFactorAuthentication
{
    public function __invoke(User $user, string $secret): void
    {
        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->getKey(),
        ], [
            'secret' => Crypt::encrypt($secret),
            'recovery_codes' => Crypt::encrypt(
                json_encode(Collection::times(8, fn () => RecoveryCode::generate())->all())
            ),
        ]);
    }
}
