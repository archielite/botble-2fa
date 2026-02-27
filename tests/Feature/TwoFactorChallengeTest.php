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

class TwoFactorChallengeTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->admin = $this->createAdminUser();
    }

    public function test_challenge_page_redirects_without_session(): void
    {
        $response = $this->get(route('two-factor.challenge'));

        $response->assertRedirect(route('access.login'));
    }

    public function test_challenge_page_is_accessible_with_session(): void
    {
        $this->withSession(['login.id' => $this->admin->getKey()]);

        $response = $this->get(route('two-factor.challenge'));

        $response->assertOk();
    }

    public function test_challenge_with_valid_code_logs_in_user(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->admin->getKey())
            ->where('authenticatable_type', get_class($this->admin))
            ->first();
        $twoFactor->forceFill(['confirmed_at' => now()])->save();

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->withSession([
            'login.id' => $this->admin->getKey(),
            'login.remember' => false,
        ])->postJson(route('two-factor.challenge'), [
            'code' => '123456',
        ]);

        $response->assertSuccessful();
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_challenge_with_recovery_code_logs_in_user(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->admin->getKey())
            ->where('authenticatable_type', get_class($this->admin))
            ->first();
        $twoFactor->forceFill(['confirmed_at' => now()])->save();

        $recoveryCode = $twoFactor->recoveryCodes()[0];

        $response = $this->withSession([
            'login.id' => $this->admin->getKey(),
            'login.remember' => false,
        ])->postJson(route('two-factor.challenge'), [
            'recovery_code' => $recoveryCode,
        ]);

        $response->assertSuccessful();
        $this->assertAuthenticatedAs($this->admin);
    }

    public function test_challenge_with_invalid_code_fails(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->admin->getKey())
            ->where('authenticatable_type', get_class($this->admin))
            ->first();
        $twoFactor->forceFill(['confirmed_at' => now()])->save();

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '000000')->andReturn(false);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $response = $this->withSession([
            'login.id' => $this->admin->getKey(),
            'login.remember' => false,
        ])->postJson(route('two-factor.challenge'), [
            'code' => '000000',
        ]);

        $response->assertJsonValidationErrors('code');
    }

    public function test_challenge_clears_session_after_successful_login(): void
    {
        $secret = 'JBSWY3DPEHPK3PXP';
        app(EnableTwoFactorAuthentication::class)($this->admin, $secret);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->admin->getKey())
            ->where('authenticatable_type', get_class($this->admin))
            ->first();
        $twoFactor->forceFill(['confirmed_at' => now()])->save();

        $mock = Mockery::mock(TwoFactorAuthenticationProvider::class);
        $mock->shouldReceive('verify')->with($secret, '123456')->andReturn(true);
        $this->app->instance(TwoFactorAuthenticationProvider::class, $mock);

        $this->withSession([
            'login.id' => $this->admin->getKey(),
            'login.remember' => true,
        ])->postJson(route('two-factor.challenge'), [
            'code' => '123456',
        ]);

        $this->assertNull(session('login.id'));
        $this->assertNull(session('login.remember'));
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
