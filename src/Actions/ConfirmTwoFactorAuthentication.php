<?php

namespace Botble\TwoFa\Actions;

use Botble\ACL\Models\User;
use Botble\TwoFa\Models\TwoFactorAuthentication;
use Botble\TwoFa\TwoFactorAuthenticationProvider;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __invoke(User $user, string $code): void
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('user_id', $user->id)
            ->first();

        if (
            empty($twoFactor->secret)
            || empty($code)
            || ! app(TwoFactorAuthenticationProvider::class)->verify(decrypt($twoFactor->secret), $code)
        ) {
            throw ValidationException::withMessages([
                'code' => [__('The provided two factor authentication code was invalid.')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $twoFactor->forceFill([
            'confirmed_at' => Carbon::now(),
        ])->save();
    }
}
