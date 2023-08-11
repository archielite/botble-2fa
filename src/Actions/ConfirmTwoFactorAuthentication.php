<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\ACL\Models\User;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __invoke(User $user, string $code, string $secret = null): void
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('user_id', $user->id)
            ->first();

        $secret = $secret ?? decrypt($twoFactor->secret);

        if (
            empty($secret)
            || empty($code)
            || ! app(TwoFactorAuthenticationProvider::class)->verify($secret, $code)
        ) {
            throw ValidationException::withMessages([
                'code' => [trans('plugins/2fa::2fa.invalid_code')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $twoFactor->forceFill([
            'confirmed_at' => Carbon::now(),
        ])->save();
    }
}
