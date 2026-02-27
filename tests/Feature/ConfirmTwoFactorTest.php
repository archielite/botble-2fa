<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Actions\ConfirmTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ConfirmTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->user = $this->createUser();
    }

    public function test_confirm_with_valid_totp_code_sets_confirmed_at(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->user, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, '123456');

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $this->assertNotNull($twoFactor->confirmed_at);
    }

    public function test_confirm_with_invalid_code_throws_validation_exception(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->user, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '000000')->andReturn(false);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $this->expectException(ValidationException::class);

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, '000000');
    }

    public function test_confirm_with_valid_recovery_code(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->user, 'JBSWY3DPEHPK3PXP');

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $recoveryCodes = $twoFactor->recoveryCodes();
        $validRecoveryCode = $recoveryCodes[0];

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, $validRecoveryCode);

        $twoFactor->refresh();

        $this->assertNotNull($twoFactor->confirmed_at);
        $this->assertNotContains($validRecoveryCode, $twoFactor->recoveryCodes());
    }

    public function test_confirm_with_invalid_recovery_code_throws_exception(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->user, 'JBSWY3DPEHPK3PXP');

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->andReturn(false);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $this->expectException(ValidationException::class);

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, 'invalidcode-invalidcode');
    }

    public function test_confirm_without_two_factor_record_throws_exception(): void
    {
        $this->expectException(ValidationException::class);

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, '123456');
    }

    public function test_confirm_with_explicit_secret_stores_it(): void
    {
        app(EnableTwoFactorAuthentication::class)($this->user, 'JBSWY3DPEHPK3PXP');

        $newSecret = 'KRSXG5DJMM6Q6RZP';

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($newSecret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $action = app(ConfirmTwoFactorAuthentication::class);
        $action($this->user, '123456', $newSecret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $this->assertEquals($newSecret, Crypt::decrypt($twoFactor->secret));
        $this->assertNotNull($twoFactor->confirmed_at);
    }

    protected function activatePlugins(): void
    {
        $plugins = array_values(BaseHelper::scanFolder(plugin_path()));

        Setting::forceSet('activated_plugins', json_encode($plugins))->save();
    }

    protected function createUser(): User
    {
        Schema::disableForeignKeyConstraints();
        User::query()->truncate();

        $user = new User();
        $user->forceFill([
            'first_name' => 'Test',
            'last_name' => 'Admin',
            'email' => 'admin@test.com',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'super_user' => 1,
            'manage_supers' => 1,
        ]);
        $user->save();

        return $user;
    }
}
