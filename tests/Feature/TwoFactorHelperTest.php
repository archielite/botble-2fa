<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TwoFactorHelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
    }

    public function test_is_setting_enabled_returns_false_by_default(): void
    {
        $this->assertFalse(TwoFactor::isSettingEnabled());
    }

    public function test_is_setting_enabled_returns_true_when_enabled(): void
    {
        Setting::forceSet('2fa_enabled', true);
        Setting::save();

        $this->assertTrue(TwoFactor::isSettingEnabled());
    }

    public function test_user_has_enabled_returns_false_when_no_record(): void
    {
        $user = $this->createUser();

        $this->assertFalse(TwoFactor::userHasEnabled($user));
    }

    public function test_user_has_enabled_returns_false_when_not_confirmed(): void
    {
        $user = $this->createUser();

        TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode([])),
            'confirmed_at' => null,
        ]);

        $this->assertFalse(TwoFactor::userHasEnabled($user));
    }

    public function test_user_has_enabled_returns_true_when_confirmed(): void
    {
        $user = $this->createUser();

        TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode([])),
            'confirmed_at' => now(),
        ]);

        $this->assertTrue(TwoFactor::userHasEnabled($user));
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
