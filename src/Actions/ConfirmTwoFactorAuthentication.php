<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\ACL\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __invoke(User $user, string $code, string $secret = null): void
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('user_id', $user->getKey())
            ->first();

        $secret ??= Crypt::decrypt($twoFactor->secret);

        if ($recoveryCode = $this->validRecoveryCode($twoFactor, $code)) {
            $twoFactor->replaceRecoveryCode($recoveryCode);
        } elseif (! $this->hasValidCode($code, $secret)) {
            throw ValidationException::withMessages([
                'code' => [trans('plugins/2fa::2fa.invalid_code')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $twoFactor->forceFill([
            'confirmed_at' => Carbon::now(),
        ])->save();
    }

    protected function hasValidCode(string|null $code = null, string $secret): bool
    {
        return $code && tap(app(TwoFactorAuthenticationProvider::class)->verify($secret, $code), function ($result) {
            if ($result) {
                session()->forget('login.id');
            }
        });
    }

    protected function validRecoveryCode(TwoFactorAuthentication $twoFactor, string $recoveryCode): string|null
    {
        return tap(
            collect($twoFactor->recoveryCodes())
                ->first(fn ($code) => hash_equals($code, $recoveryCode) ? $code : null),
            function ($code) {
                if ($code) {
                    session()->forget('login.id');
                }
            }
        );
    }
}
