<?php

namespace Botble\TwoFa\Actions;

use Botble\ACL\Models\User;
use Botble\TwoFa\Contracts\TwoFactorAuthenticationProvider;
use Botble\TwoFa\Models\TwoFactorAuthentication;
use Botble\TwoFa\RecoveryCode;
use Illuminate\Support\Collection;

class EnableTwoFactorAuthentication
{
    public function __invoke(User $user): void
    {
        TwoFactorAuthentication::query()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'secret' => encrypt(app(TwoFactorAuthenticationProvider::class)->generateSecretKey()),
            'recovery_codes' => encrypt(json_encode(Collection::times(8, function () {
                return RecoveryCode::generate();
            })->all())),
        ]);
    }
}
