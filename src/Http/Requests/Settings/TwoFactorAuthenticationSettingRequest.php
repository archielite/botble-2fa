<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Requests\Settings;

use Botble\Base\Rules\OnOffRule;
use Botble\Support\Http\Requests\Request;

class TwoFactorAuthenticationSettingRequest extends Request
{
    public function rules(): array
    {
        return [
            '2fa_enabled' => [new OnOffRule()],
        ];
    }
}
