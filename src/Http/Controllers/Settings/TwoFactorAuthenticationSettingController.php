<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Controllers\Settings;

use ArchiElite\TwoFactorAuthentication\Forms\Settings\TwoFactorAuthenticationSettingForm;
use ArchiElite\TwoFactorAuthentication\Http\Requests\Settings\TwoFactorAuthenticationSettingRequest;
use Botble\Setting\Http\Controllers\SettingController;

class TwoFactorAuthenticationSettingController extends SettingController
{
    public function edit()
    {
        $this->pageTitle(trans('plugins/2fa::2fa.settings.title'));

        return TwoFactorAuthenticationSettingForm::create()->renderForm();
    }

    public function update(TwoFactorAuthenticationSettingRequest $request)
    {
        return $this->performUpdate($request->validated());
    }
}
