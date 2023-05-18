<?php

namespace ArchiElite\TwoFactorAuthentication;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Illuminate\Contracts\Auth\Authenticatable;

class TwoFactor
{
    public static function isSettingEnabled(): bool
    {
        return (bool) setting('2fa_enabled', false);
    }

    public static function userHasEnabled(Authenticatable $user): bool
    {
        return TwoFactorAuthentication::query()
            ->where('user_id', $user->getAuthIdentifier())
            ->whereNotNull('confirmed_at')
            ->exists();
    }
}
