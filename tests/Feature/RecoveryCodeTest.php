<?php

namespace ArchiElite\TwoFactorAuthentication\Tests\Feature;

use ArchiElite\TwoFactorAuthentication\RecoveryCode;
use Tests\TestCase;

class RecoveryCodeTest extends TestCase
{
    public function test_generates_recovery_code_with_correct_format(): void
    {
        $code = RecoveryCode::generate();

        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{10}-[a-zA-Z0-9]{10}$/', $code);
    }

    public function test_generates_unique_recovery_codes(): void
    {
        $codes = array_map(fn () => RecoveryCode::generate(), range(1, 50));

        $this->assertCount(50, array_unique($codes));
    }

    public function test_recovery_code_has_correct_length(): void
    {
        $code = RecoveryCode::generate();

        $this->assertEquals(21, strlen($code));
    }
}
