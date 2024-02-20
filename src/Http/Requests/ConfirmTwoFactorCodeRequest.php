<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Requests;

use Botble\Support\Http\Requests\Request;

class ConfirmTwoFactorCodeRequest extends Request
{
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'numeric', 'digits:6'],
            'recovery_code' => ['sometimes', 'required', 'string'],
        ];
    }
}
