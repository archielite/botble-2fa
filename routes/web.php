<?php

use ArchiElite\TwoFactorAuthentication\Http\Controllers\ConfirmedTwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerConfirmedTwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerRecoveryCodeController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerTwoFactorAuthenticatedSessionController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerTwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerTwoFactorQrCodeController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\CustomerTwoFactorSettingsController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\RecoveryCodeController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\Settings\TwoFactorAuthenticationSettingController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticatedSessionController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorAuthenticationController;
use ArchiElite\TwoFactorAuthentication\Http\Controllers\TwoFactorQrCodeController;
use Botble\Base\Facades\AdminHelper;
use Botble\Theme\Facades\Theme;
use Illuminate\Support\Facades\Route;

AdminHelper::registerRoutes(function (): void {
    Route::prefix('two-factor')->name('two-factor.')->group(function (): void {
        Route::prefix('system/users')->name('system.users.')->middleware('auth')->group(function (): void {
            Route::group(['permission' => false], function (): void {
                Route::post('authentication', [TwoFactorAuthenticationController::class, 'store'])
                    ->name('enable');

                Route::delete('authentication', [TwoFactorAuthenticationController::class, 'destroy'])
                    ->name('disable');

                Route::get('qr-code', [TwoFactorQrCodeController::class, 'show'])
                    ->name('qr-code');

                Route::post(
                    'confirmed-two-factor-authentication',
                    [ConfirmedTwoFactorAuthenticationController::class, 'store']
                )
                    ->name('confirm');

                Route::get('two-factor-recovery-codes', [RecoveryCodeController::class, 'index'])
                    ->name('recovery-codes');
            });
        });

        Route::group(['middleware' => 'auth', 'permission' => 'two-factor-authentication.settings'], function (): void {
            Route::get('settings', [TwoFactorAuthenticationSettingController::class, 'edit'])->name('settings');
            Route::put('settings', [TwoFactorAuthenticationSettingController::class, 'update'])->name(
                'settings.update'
            );
        });

        Route::group(['middleware' => 'guest'], function (): void {
            Route::get('challenge', [TwoFactorAuthenticatedSessionController::class, 'create'])
                ->name('challenge');

            Route::post('challenge', [TwoFactorAuthenticatedSessionController::class, 'store']);
        });
    });
}, ['web', 'core']);

Theme::registerRoutes(function (): void {
    Route::prefix('customer/two-factor')->name('customer.two-factor.')->group(function (): void {
        Route::middleware(['customer'])->group(function (): void {
            Route::get('settings', [
                CustomerTwoFactorSettingsController::class,
                'index',
            ])->name('settings');

            Route::post('enable', [
                CustomerTwoFactorAuthenticationController::class,
                'store',
            ])->name('enable');

            Route::delete('disable', [
                CustomerTwoFactorAuthenticationController::class,
                'destroy',
            ])->name('disable');

            Route::get('qr-code', [
                CustomerTwoFactorQrCodeController::class,
                'show',
            ])->name('qr-code');

            Route::post('confirm', [
                CustomerConfirmedTwoFactorAuthenticationController::class,
                'store',
            ])->name('confirm');

            Route::get('recovery-codes', [
                CustomerRecoveryCodeController::class,
                'index',
            ])->name('recovery-codes');
        });

        Route::middleware(['customer.guest'])->group(function (): void {
            Route::get('challenge', [
                CustomerTwoFactorAuthenticatedSessionController::class,
                'create',
            ])->name('challenge');

            Route::post('challenge', [
                CustomerTwoFactorAuthenticatedSessionController::class,
                'store',
            ]);
        });
    });
});
