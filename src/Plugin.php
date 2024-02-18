<?php

namespace ArchiElite\TwoFactorAuthentication;

use Botble\PluginManagement\Abstracts\PluginOperationAbstract;
use Botble\Setting\Facades\Setting;
use Illuminate\Support\Facades\Schema;

class Plugin extends PluginOperationAbstract
{
    public static function remove(): void
    {
        Schema::dropIfExists('two_factor_authentications');

        Setting::delete(['2fa_enabled']);
    }
}
