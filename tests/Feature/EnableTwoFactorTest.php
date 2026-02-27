<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Actions\CreateTwoFactorRecord;
use ArchiElite\TwoFactorAuthentication\Actions\EnableTwoFactorAuthentication;
use ArchiElite\TwoFactorAuthentication\Models\TwoFactorAuthentication;
use Botble\ACL\Models\User;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class EnableTwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->user = $this->createUser();
    }

    public function test_create_two_factor_record_creates_record_with_recovery_codes(): void
    {
        $action = app(CreateTwoFactorRecord::class);
        $encryptedSecret = $action($this->user);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $this->assertNotNull($twoFactor);
        $this->assertNotNull($twoFactor->recovery_codes);
        $this->assertCount(8, $twoFactor->recoveryCodes());
        $this->assertNull($twoFactor->confirmed_at);

        $secret = Crypt::decrypt($encryptedSecret);
        $this->assertNotEmpty($secret);
    }

    public function test_enable_two_factor_creates_record_with_secret_and_recovery_codes(): void
    {
        $action = app(EnableTwoFactorAuthentication::class);
        $action($this->user, 'JBSWY3DPEHPK3PXP');

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $this->assertNotNull($twoFactor);
        $this->assertEquals('JBSWY3DPEHPK3PXP', Crypt::decrypt($twoFactor->secret));
        $this->assertCount(8, $twoFactor->recoveryCodes());
    }

    public function test_enable_two_factor_updates_existing_record(): void
    {
        $action = app(EnableTwoFactorAuthentication::class);
        $action($this->user, 'JBSWY3DPEHPK3PXP');
        $action($this->user, 'KRSXG5DJMM6Q6RZP');

        $count = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->count();

        $this->assertEquals(1, $count);

        $twoFactor = TwoFactorAuthentication::query()
            ->where('authenticatable_id', $this->user->getKey())
            ->where('authenticatable_type', get_class($this->user))
            ->first();

        $this->assertEquals('KRSXG5DJMM6Q6RZP', Crypt::decrypt($twoFactor->secret));
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
