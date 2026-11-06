<?php

/**
 * TwoFactor.php
 *
 * Two-Factor Authentication Library
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @license GPL
 *
 * @link       https://www.librenms.org
 *
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @copyright  2017 Tony Murray
 */

namespace LibreNMS\Authentication;

use DateTimeImmutable;
use OTPHP\HOTP;
use OTPHP\TOTP;
use Psr\Clock\ClockInterface;

class TwoFactor
{
    /**
     * Key Interval in seconds.
     * Set to 30s due to Google-Authenticator limitation.
     * Sadly Google-Auth is the most used Non-Physical OTP app.
     */
    const KEY_INTERVAL = 30;

    /**
     * Time based leeway in seconds, accepts the codes valid this long before and after now.
     * Must be less than KEY_INTERVAL.
     */
    const TOTP_LEEWAY = 29;

    /**
     * Counter based lookahead, the user's counter may be ahead of the server's.
     */
    const HOTP_WINDOW = 4;

    /**
     * Generate Secret Key
     * 160 bits as recommended by RFC 4226, base32 encoded without padding
     */
    public static function genKey(): string
    {
        return HOTP::generate(20)->getSecret();
    }

    /**
     * Verify HOTP token honouring window
     *
     * @param  string  $key  Secret Key
     * @param  int|string  $otp  OTP supplied by user
     * @param  int|false  $counter  Counter, if false time based (TOTP) is used
     * @return bool|int true if valid, false if invalid, the matched counter for counter based if ahead of $counter
     */
    public static function verifyHOTP($key, $otp, $counter = false): bool|int
    {
        $otp = (string) $otp;
        if ($otp === '') {
            return false;
        }

        if ($counter === false) {
            return TOTP::createFromSecret($key, self::clock())->verify($otp, null, self::TOTP_LEEWAY);
        }

        $counter = max(0, (int) $counter);
        $hotp = HOTP::createFromSecret($key);
        if (! $hotp->verify($otp, $counter, self::HOTP_WINDOW)) {
            return false;
        }

        // verify() moves the counter past the matched one
        $matched = $hotp->getCounter() - 1;

        return $matched === $counter ? true : $matched;
    }

    /**
     * The application clock, so the time can be set in tests
     */
    private static function clock(): ClockInterface
    {
        return new class implements ClockInterface
        {
            public function now(): DateTimeImmutable
            {
                return now()->toDateTimeImmutable();
            }
        };
    }

    /**
     * Generate 2fa URI
     *
     * @param  string  $username
     * @param  string  $key
     * @param  int|bool  $counter  the server's counter for counter based, false for time based
     */
    public static function generateUri($username, $key, int|bool $counter = false): string
    {
        $title = 'LibreNMS:' . urlencode($username);

        if ($counter === false) {
            return "otpauth://totp/$title?issuer=LibreNMS&secret=$key";
        }

        // start the app at the server's counter so they are in sync
        $counter = $counter === true ? 0 : max(0, $counter);

        return "otpauth://hotp/$title?issuer=LibreNMS&counter=$counter&secret=$key";
    }
}
