<x-core-setting::section
    :title="trans('Two Factor Authentication')"
    :description="trans('Add an extra layer of security to your account using two factor authentication.')"
>
    <x-core-setting::on-off
        name="2fa_enabled"
        :label="trans('Enable Two Factor Authentication')"
        :value="\Botble\TwoFa\TwoFa::isSettingEnabled()"
    />
</x-core-setting::section>
