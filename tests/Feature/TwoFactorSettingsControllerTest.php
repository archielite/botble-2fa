<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use Botble\ACL\Models\User;
use Botble\ACL\Services\ActivateUserService;
use Botble\Base\Facades\BaseHelper;
use Botble\Setting\Facades\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TwoFactorSettingsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->activatePlugins();
        $this->admin = $this->createAdminUser();
    }

    public function test_settings_page_is_accessible(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('two-factor.settings'));

        $response->assertOk();
    }

    public function test_settings_can_be_updated_to_enabled(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('two-factor.settings.update'), [
            '2fa_enabled' => '1',
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertEquals('1', setting('2fa_enabled'));
    }

    public function test_settings_can_be_updated_to_disabled(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('two-factor.settings.update'), [
            '2fa_enabled' => '0',
        ]);

        $response->assertSessionHasNoErrors();
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
