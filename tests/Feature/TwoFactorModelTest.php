<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TwoFactorModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
    }

    public function test_can_create_two_factor_record(): void
    {
        $user = $this->createUser();

        TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode(['code1-code1', 'code2-code2'])),
        ]);

        $this->assertDatabaseHas('two_factor_authentications', [
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
        ]);
    }

    public function test_recovery_codes_returns_decrypted_array(): void
    {
        $user = $this->createUser();
        $codes = ['abcdefghij-klmnopqrst', 'uvwxyz1234-5678901234'];

        $twoFactor = TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode($codes)),
        ]);

        $this->assertEquals($codes, $twoFactor->recoveryCodes());
    }

    public function test_replace_recovery_code_replaces_used_code(): void
    {
        $user = $this->createUser();
        $codes = ['abcdefghij-klmnopqrst', 'uvwxyz1234-5678901234'];

        $twoFactor = TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode($codes)),
        ]);

        $twoFactor->replaceRecoveryCode('abcdefghij-klmnopqrst');
        $twoFactor->refresh();

        $updatedCodes = $twoFactor->recoveryCodes();

        $this->assertCount(2, $updatedCodes);
        $this->assertNotContains('abcdefghij-klmnopqrst', $updatedCodes);
        $this->assertContains('uvwxyz1234-5678901234', $updatedCodes);
    }

    public function test_confirmed_at_is_cast_to_datetime(): void
    {
        $user = $this->createUser();

        $twoFactor = TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode([])),
            'confirmed_at' => now(),
        ]);

        $this->assertInstanceOf(Carbon::class, $twoFactor->confirmed_at);
    }

    public function test_authenticatable_morphto_relationship(): void
    {
        $user = $this->createUser();

        $twoFactor = TwoFactorAuthentication::query()->create([
            'authenticatable_id' => $user->getKey(),
            'authenticatable_type' => get_class($user),
            'secret' => Crypt::encrypt('JBSWY3DPEHPK3PXP'),
            'recovery_codes' => Crypt::encrypt(json_encode([])),
        ]);

        $this->assertInstanceOf(User::class, $twoFactor->authenticatable);
        $this->assertEquals($user->getKey(), $twoFactor->authenticatable->getKey());
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
