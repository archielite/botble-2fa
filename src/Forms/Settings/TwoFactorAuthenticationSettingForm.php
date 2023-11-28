<?php

namespace ArchiElite\TwoFactorAuthentication\Forms\Settings;

use ArchiElite\TwoFactorAuthentication\Http\Requests\Settings\TwoFactorAuthenticationSettingRequest;
use ArchiElite\TwoFactorAuthentication\TwoFactor;
use Botble\Base\Forms\FieldOptions\CheckboxFieldOption;
use Botble\Base\Forms\Fields\OnOffCheckboxField;
use Botble\Setting\Forms\SettingForm;

class TwoFactorAuthenticationSettingForm extends SettingForm
{
    public function buildForm(): void
    {
        parent::buildForm();

        $this
            ->setSectionTitle(trans('plugins/2fa::2fa.settings.title'))
            ->setSectionDescription(trans('plugins/2fa::2fa.settings.description'))
            ->setValidatorClass(TwoFactorAuthenticationSettingRequest::class)
            ->add(
                '2fa_enabled',
                OnOffCheckboxField::class,
                CheckboxFieldOption::make()
                    ->label(trans('plugins/2fa::2fa.settings.enable_global'))
                    ->value(TwoFactor::isSettingEnabled())
                    ->toArray()
            );
    }
}
