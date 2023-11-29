<?php

use ArchiElite\TwoFactorAuthentication\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\RecoveryCodeController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticatedSessionController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorQrCodeController;
use Botble\Base\Facades\AdminHelper;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\Settings\TwoFactorAuthenticationSettingController;
use Illuminate\Support\Facades\Route;

Route::prefix('two-factor')->name('two-factor.')->group(function () {
    AdminHelper::registerRoutes(function () {
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

        Route::group(['permission' => 'two-factor-authentication.settings'], function () {
            Route::get('settings', [TwoFactorAuthenticationSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [TwoFactorAuthenticationSettingController::class, 'update'])->name('settings.update');
        });
    });

    Route::middleware(['web', 'core', 'guest'])->group(function () {
        Route::get('challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
            ->name('challenge');

        Route::post('challenge', [TwoFactorAuthenticatedSessionController::class, 'store']);
    });
});
