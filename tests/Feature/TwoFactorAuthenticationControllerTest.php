<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactorAuthenticationProvider;
use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

class TwoFactorAuthenticationControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->admin = $this->createAdminUser();
    }

    public function test_can_enable_two_factor_authentication(): void
    {
        $this->actingAs($this->admin);

        $response = $this->postJson(route('two-factor.system.users.enable'), [
            'secret' => 'JBSWY3DPEHPK3PXP',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseHas('two_factor_authentications', [
            'authenticatable_id' => $this->admin->getKey(),
            'authenticatable_type' => get_class($this->admin),
        ]);
    }

    public function test_can_disable_two_factor_authentication(): void
    {
        $this->actingAs($this->admin);

        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->deleteJson(route('two-factor.system.users.disable'), [
            'code' => '123456',
        ]);

        $response->assertSuccessful();

        $this->assertDatabaseMissing('two_factor_authentications', [
            'authenticatable_id' => $this->admin->getKey(),
            'authenticatable_type' => get_class($this->admin),
        ]);
    }

    public function test_can_confirm_two_factor_authentication(): void
    {
        $this->actingAs($this->admin);

        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '654321')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->postJson(route('two-factor.system.users.confirm'), [
            'code' => '654321',
        ]);

        $response->assertSuccessful();

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->admin->getKey())
            ->where('authenticatable_type', get_class($this->admin))
            ->first();

        $this->assertNotNull($twoFactor->confirmed_at);
    }

    public function test_confirm_with_invalid_code_returns_validation_error(): void
    {
        $this->actingAs($this->admin);

        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '000000')->andReturn(false);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->postJson(route('two-factor.system.users.confirm'), [
            'code' => '000000',
        ]);

        $response->assertJsonValidationErrors('code');
    }

    public function test_can_get_qr_code(): void
    {
        $this->actingAs($this->admin);

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('generateSecretKey')->andReturn('JBSWY3DPEHPK3PXP');
        $mock->shouldReceive('qrCodeUrl')
            ->andReturn('otpauth://totp/App:admin@test.com?secret=JBSWY3DPEHPK3PXP');
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->getJson(route('two-factor.system.users.qr-code'));

        $response->assertSuccessful();
        $response->assertJsonStructure(['data' => ['svg', 'url', 'secret']]);
    }

    protected function activatePlugins(): void
    {
        $plugins = array_values(BaseHelper::scanFolder(plugin_path()));

        Setting::forceSet('activated_plugins', json_encode($plugins))->save();
    }

    protected function createAdminUser(): User
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

        app(ActivateUserService::class)->activate($user);

        return $user;
    }
}
