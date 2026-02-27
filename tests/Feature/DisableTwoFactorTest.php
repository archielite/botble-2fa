<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Actions\DisableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class DisableTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->user = $this->createUser();
    }

    public function test_disable_with_valid_code_deletes_record(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->user, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $action = app(DisableTwoFactorAuthentication::class);
        $action($this->user, '123456');

        $this->assertDatabaseMissing('two_factor_authentications', [
            'authenticatable_id' => $this->user->getKey(),
            'authenticatable_type' => get_class($this->user),
        ]);
    }

    public function test_disable_with_invalid_code_throws_exception(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->user, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '000000')->andReturn(false);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $this->expectException(ValidationException::class);

        $action = app(DisableTwoFactorAuthentication::class);
        $action($this->user, '000000');
    }

    public function test_disable_with_recovery_code_deletes_record(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->user, $secret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $recoveryCode = $twoFactor->recoveryCodes()[0];

        $action = app(DisableTwoFactorAuthentication::class);
        $action($this->user, $recoveryCode);

        $this->assertDatabaseMissing('two_factor_authentications', [
            'authenticatable_id' => $this->user->getKey(),
            'authenticatable_type' => get_class($this->user),
        ]);
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
