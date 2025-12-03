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
            ->where('authenticatable_id', $user->getAuthIdentifier())
            ->where('authenticatable_type', get_class($user))
            ->whereNotNull('confirmed_at')
            ->exists();
    }
}
