<?php

namespace ArchiElite\TwoFactorAuthentication\Contracts;

interface TwoFactorAuthenticationProvider
{
    public function generateSecretKey(): string;

    public function qrCodeUrl(string $companyName, string $companyEmail, string $secret): string;

    public function verify(string $secret, string $code): bool;
}
