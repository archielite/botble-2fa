<?php

use Botble\Base\Facades\BaseHelper;
use Botble\TwoFactorAuthentication\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use Botble\TwoFactorAuthentication\Http\Controllers\RecoveryCodeController;
use Botble\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticatedSessionController;
use Botble\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticationController;
use Botble\TwoFactorAuthentication\Http\Controllers\TwoFactorQrCodeController;
use Botble\TwoFactorAuthentication\TwoFactor;

if (! TwoFactor::isSettingEnabled()) {
    return;
}

Route::prefix(BaseHelper::getAdminPrefix())->middleware(['web', 'core'])->group(function () {
    Route::prefix('two-factor')->name('two-factor.')->group(function () {
        Route::prefix('system/users')->name('system.users.')->middleware('auth')->group(function () {
            Route::post('authentication', [TwoFactorAuthenticationController::class, 'store'])
                ->name('enable');

            Route::delete('authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                ->name('disable');

            Route::get('qr-code', [TwoFactorQrCodeController::class, 'show'])
                ->name('qr-code');

            Route::post('confirmed-two-factor-authentication', [ConfirmedTwoFactorAuthenticationController::class, 'store'])
                ->name('confirm');

            Route::get('two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
                ->name('recovery-codes');
        });

        Route::middleware('guest')->group(function () {
            Route::get('challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->name('challenge');

            Route::post('challenge', [TwoFactorAuthenticatedSessionController::class, 'store']);
        });
    });
});
