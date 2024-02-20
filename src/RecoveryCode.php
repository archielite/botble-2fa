<?php

namespace ArchiElite\TwoFactorAuthentication;

use Illuminate\Support\Str;

class RecoveryCode
{
    public static function generate(): string
    {
        return Str::random(10) . '-' . Str::random(10);
    }
}

// BhHlXdLF4Q-jHZgui8teH
// f6kRSm7A3J-jF2g2P0XoD
// ELUQiuCT1N-Dc8SElx7iT
// zsMz3AeoBd-UccwhVYWRF
// z8rxzlSCfT-O7eEwU8sX6
// gtZyAGzuWd-RNtntquswc
// aSnbDMvqEu-yQbOmiTQrQ
// AMgnTco72C-abCNixxQIA
