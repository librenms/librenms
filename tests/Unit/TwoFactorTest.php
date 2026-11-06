<?php

namespace LibreNMS\Tests\Unit;

use Illuminate\Support\Carbon;
use LibreNMS\Authentication\TwoFactor;
use LibreNMS\Tests\TestCase;

final class TwoFactorTest extends TestCase
{
    // RFC 4226 Appendix D secret and codes, used as known fixtures
    // "12345678901234567890" in base32
    private const string RFC_KEY = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    private const array RFC_HOTP = [
        '755224', '287082', '359152', '969429', '338314',
        '254676', '287922', '162583', '399871', '520489',
    ];

    public function testCounterLookaheadReturnsMatchedCounter(): void
    {
        $this->assertSame(3, TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[3], 1));
        $this->assertSame(5, TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[5], 1));
    }

    public function testCounterRejectsOutsideWindow(): void
    {
        // lookahead is 4, no lookbehind
        $this->assertFalse(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[6], 1));
        $this->assertFalse(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[2], 3));
    }

    public function testTimeBasedAllowsLeeway(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(3 * 30 + 10)); // step 3, 10s in

        $this->assertTrue(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[2])); // previous step, within 29s
        $this->assertTrue(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[4])); // next step, within 29s
    }

    public function testTimeBasedRejectsOutsideLeeway(): void
    {
        Carbon::setTestNow(Carbon::createFromTimestamp(3 * 30 + 29)); // step 3, 29s in

        $this->assertFalse(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[2])); // previous step, 30s ago
        $this->assertFalse(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[1]));
        $this->assertFalse(TwoFactor::verifyHOTP(self::RFC_KEY, self::RFC_HOTP[5]));
    }

    public function testGeneratedKeyIsUsable(): void
    {
        $key = TwoFactor::genKey();

        $this->assertMatchesRegularExpression('/^[A-Z2-7]{32}$/', $key); // 160 bits, unpadded base32
        $this->assertNotSame($key, TwoFactor::genKey());
    }

    public function testGenerateUri(): void
    {
        $this->assertSame(
            'otpauth://totp/LibreNMS:my+user?issuer=LibreNMS&secret=' . self::RFC_KEY,
            TwoFactor::generateUri('my user', self::RFC_KEY)
        );
        $this->assertSame(
            'otpauth://hotp/LibreNMS:my+user?issuer=LibreNMS&counter=0&secret=' . self::RFC_KEY,
            TwoFactor::generateUri('my user', self::RFC_KEY, 0)
        );
        $this->assertSame(
            'otpauth://hotp/LibreNMS:my+user?issuer=LibreNMS&counter=42&secret=' . self::RFC_KEY,
            TwoFactor::generateUri('my user', self::RFC_KEY, 42)
        );
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }
}
