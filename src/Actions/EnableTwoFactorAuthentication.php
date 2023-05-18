<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use Botble\ACL\Models\User;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
{
    public function __invoke(User $user, string $secret): void
    {
        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'secret' => encrypt($secret),
            'recovery_codes' => encrypt(
                json_encode(
                    Collection::times(8, function () {
                        return RecoveryCode::generate();
                    })->all()
                )
            ),
        ]);
    }
}
