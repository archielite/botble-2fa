<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use Botble\ACL\Models\User;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;

class GenerateTwoFactorQrCodeUrl
{
    public function __invoke(User $user, string $secret): string
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            config('app.name'),
            $user->email,
            decrypt($secret)
        );
    }
}
