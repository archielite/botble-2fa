<?php

namespace ArchiElite\TwoFactorAuthentication\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ConfirmTwoFactorCodeRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'code' => ['sometimes', 'required', 'numeric', 'digits:6'],
            'recovery_code' => ['sometimes', 'required', 'string'],
        ];
    }
}
