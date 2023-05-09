<?php

namespace Botble\TwoFa;

use Illuminate\Support\Str;

class RecoveryCode
{
    public static function generate(): string
    {
        return Str::random(10) . '-' . Str::random(10);
    }
}
