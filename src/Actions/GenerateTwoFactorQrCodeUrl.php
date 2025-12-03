<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class GenerateTwoFactorQrCodeUrl
{
    public function __invoke(Authenticatable $user, string $secret): string
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            config('app.name'),
            $user->email,
            decrypt($secret)
        );
    }
}
