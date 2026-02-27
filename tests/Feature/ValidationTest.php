<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\Http\Requests\ConfirmTwoFactorCodeRequest;
use ArchiElite\TwoFactorAuthentication\Http\Requests\Settings\TwoFactorAuthenticationSettingRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ValidationTest extends TestCase
{
    public function test_confirm_code_accepts_six_digit_numeric_code(): void
    {
        $rules = (new ConfirmTwoFactorCodeRequest())->rules();

        $validator = Validator::make(['code' => '123456'], ['code' => $rules['code']]);
        $this->assertTrue($validator->passes());
    }

    public function test_confirm_code_rejects_non_numeric_code(): void
    {
        $rules = (new ConfirmTwoFactorCodeRequest())->rules();

        $validator = Validator::make(['code' => 'abcdef'], ['code' => $rules['code']]);
        $this->assertFalse($validator->passes());
    }

    public function test_confirm_code_rejects_code_with_wrong_length(): void
    {
        $rules = (new ConfirmTwoFactorCodeRequest())->rules();

        $validator = Validator::make(['code' => '12345'], ['code' => $rules['code']]);
        $this->assertFalse($validator->passes());

        $validator = Validator::make(['code' => '1234567'], ['code' => $rules['code']]);
        $this->assertFalse($validator->passes());
    }

    public function test_confirm_code_accepts_string_recovery_code(): void
    {
        $rules = (new ConfirmTwoFactorCodeRequest())->rules();

        $validator = Validator::make(
            ['recovery_code' => 'abcdefghij-klmnopqrst'],
            ['recovery_code' => $rules['recovery_code']]
        );
        $this->assertTrue($validator->passes());
    }

    public function test_confirm_code_rejects_empty_recovery_code(): void
    {
        $rules = (new ConfirmTwoFactorCodeRequest())->rules();

        $validator = Validator::make(
            ['recovery_code' => ''],
            ['recovery_code' => $rules['recovery_code']]
        );
        $this->assertFalse($validator->passes());
    }

    public function test_setting_request_accepts_valid_on_off_values(): void
    {
        $rules = (new TwoFactorAuthenticationSettingRequest())->rules();

        $validator = Validator::make(['2fa_enabled' => '1'], ['2fa_enabled' => $rules['2fa_enabled']]);
        $this->assertTrue($validator->passes());

        $validator = Validator::make(['2fa_enabled' => '0'], ['2fa_enabled' => $rules['2fa_enabled']]);
        $this->assertTrue($validator->passes());
    }

    public function test_setting_request_rejects_invalid_values(): void
    {
        $rules = (new TwoFactorAuthenticationSettingRequest())->rules();

        $validator = Validator::make(['2fa_enabled' => 'invalid'], ['2fa_enabled' => $rules['2fa_enabled']]);
        $this->assertFalse($validator->passes());
    }
}
