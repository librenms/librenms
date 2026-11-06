<?php

namespace LibreNMS\Tests\Unit;

use App\Models\User;
use App\Facades\LibrenmsConfig;
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

    public function testCounterReturnsNextCounter(): void
    {
        $this->assertSame(2, TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[1], 1));
        $this->assertSame(6, TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[5], 1)); // lookahead
    }

    public function testCounterRejectsOutsideWindow(): void
    {
        // lookahead is 4, no lookbehind
        $this->assertFalse(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[6], 1));
        $this->assertFalse(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[2], 3));
    }

    public function testTimeBasedAllowsLeeway(): void
    {
        $this->travelTo(Carbon::createFromTimestamp(3 * 30 + 10)); // step 3, 10s in

        $this->assertTrue(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[2])); // previous step, within 29s
        $this->assertTrue(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[4])); // next step, within 29s
    }

    public function testTimeBasedRejectsOutsideLeeway(): void
    {
        $this->travelTo(Carbon::createFromTimestamp(3 * 30 + 29)); // step 3, 29s in

        $this->assertFalse(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[2])); // previous step, 30s ago
        $this->assertFalse(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[1]));
        $this->assertFalse(TwoFactor::verify(self::RFC_KEY, self::RFC_HOTP[5]));
    }

    public function testGenerateUri(): void
    {
        $user = new User(['username' => 'my user', 'email' => 'me@example.com']);

        $this->assertSame(
            'otpauth://totp/LibreNMS%3Ame%40example.com?issuer=LibreNMS&secret=' . self::RFC_KEY,
            TwoFactor::generateUri($user, self::RFC_KEY)
        );
        $this->assertSame(
            'otpauth://hotp/LibreNMS%3Ame%40example.com?counter=42&issuer=LibreNMS&secret=' . self::RFC_KEY,
            TwoFactor::generateUri($user, self::RFC_KEY, 42)
        );
    }

    public function testGenerateUriLabelFallsBackToUsername(): void
    {
        $user = new User(['username' => 'domain:user', 'email' => '']);

        $this->assertStringStartsWith('otpauth://totp/LibreNMS%3Adomainuser?', TwoFactor::generateUri($user, self::RFC_KEY));
    }

    public function testGenerateUriIssuerIsProjectName(): void
    {
        LibrenmsConfig::set('project_name', 'My: NMS');
        $user = new User(['username' => 'user', 'email' => '']);

        $this->assertSame(
            'otpauth://totp/My%20NMS%3Auser?issuer=My%20NMS&secret=' . self::RFC_KEY,
            TwoFactor::generateUri($user, self::RFC_KEY)
        );
    }
}
