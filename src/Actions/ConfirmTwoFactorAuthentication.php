<?php

namespace ArchiElite\TwoFactorAuthentication\Actions;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\ValidationException;

class ConfirmTwoFactorAuthentication
{
    public function __invoke(Authenticatable $user, string $code, ?string $secret = null): void
    {
        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $user->getKey())
            ->where('authenticatable_type', get_class($user))
            ->first();

        if (! $twoFactor) {
            throw ValidationException::withMessages([
                'code' => [trans('plugins/2fa::2fa.invalid_code')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        if ($secret) {
            $decryptedSecret = $secret;
        } else {
            if (! $twoFactor->secret) {
                throw ValidationException::withMessages([
                    'code' => [trans('plugins/2fa::2fa.invalid_code')],
                ])->errorBag('confirmTwoFactorAuthentication');
            }

            try {
                $decryptedSecret = Crypt::decrypt($twoFactor->secret);
            } catch (DecryptException $e) {
                throw ValidationException::withMessages([
                    'code' => [trans('plugins/2fa::2fa.error_occurred')],
                ])->errorBag('confirmTwoFactorAuthentication');
            }
        }

        if ($recoveryCode = $this->validRecoveryCode($twoFactor, $code)) {
            $twoFactor->replaceRecoveryCode($recoveryCode);
        } elseif (! $this->hasValidCode($code, $decryptedSecret)) {
            throw ValidationException::withMessages([
                'code' => [trans('plugins/2fa::2fa.invalid_code')],
            ])->errorBag('confirmTwoFactorAuthentication');
        }

        $updateData = ['confirmed_at' => Carbon::now()];
        if ($secret) {
            try {
                $updateData['secret'] = Crypt::encrypt($secret);
            } catch (DecryptException $e) {
                throw ValidationException::withMessages([
                    'code' => [trans('plugins/2fa::2fa.error_occurred')],
                ])->errorBag('confirmTwoFactorAuthentication');
            }
        }

        $twoFactor->forceFill($updateData)->save();
    }

    protected function hasValidCode(?string $code = null, string $secret): bool
    {
        return $code && tap(app(TwoFactorAuthenticationProvider::class)->verify($secret, $code), function ($result): void {
            if ($result) {
                session()->forget('login.id');
            }
        });
    }

    protected function validRecoveryCode(TwoFactorAuthentication $twoFactor, string $recoveryCode): ?string
    {
        return tap(
            collect($twoFactor->recoveryCodes())
                ->first(fn ($code) => hash_equals($code, $recoveryCode) ? $code : null),
            function ($code): void {
                if ($code) {
                    session()->forget('login.id');
                }
            }
        );
    }
}
