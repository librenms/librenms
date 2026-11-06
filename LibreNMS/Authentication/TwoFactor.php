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

use Carbon\FactoryImmutable;
use OTPHP\HOTP;
use OTPHP\TOTP;

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
     * 160 bits as recommended by RFC 4226, base32 encoded without padding.
     * The secret is the same for time and counter based, HOTP is only used because it needs no clock.
     */
    public static function genKey(): string
    {
        return HOTP::generate(20)->getSecret();
    }

    /**
     * Verify a time based (TOTP) or counter based (HOTP) token
     *
     * @param  string  $key  Secret Key
     * @param  int|string  $otp  OTP supplied by user
     * @param  int|false  $counter  Counter, if false time based (TOTP) is used
     * @return bool|int true if valid, false if invalid, the matched counter for counter based if ahead of $counter
     */
    public static function verify($key, $otp, $counter = false): bool|int
    {
        $otp = (string) $otp;
        if ($otp === '') {
            return false;
        }

        $otphp = self::otp($key, $counter);

        if ($otphp instanceof TOTP) {
            return $otphp->verify($otp, null, self::TOTP_LEEWAY);
        }

        $counter = $otphp->getCounter();
        if (! $otphp->verify($otp, $counter, self::HOTP_WINDOW)) {
            return false;
        }

        // verify() moves the counter past the matched one
        $matched = $otphp->getCounter() - 1;

        return $matched === $counter ? true : $matched;
    }

    /**
     * Generate the 2fa URI for the QR code
     *
     * @param  string  $label  account shown in the authenticator app, such as the email or username
     * @param  string  $key
     * @param  int|bool  $counter  the server's counter for counter based, false for time based
     */
    public static function generateUri(string $label, $key, int|bool $counter = false): string
    {
        // the label may not contain a colon, it is only shown in the authenticator app
        return self::otp($key, $counter)
            ->withLabel(str_replace(':', '', $label))
            ->withIssuer('LibreNMS')
            ->getProvisioningUri();
    }

    /**
     * @param  int|bool  $counter  false for time based, the counter for counter based (true is counter 0)
     */
    private static function otp(string $key, int|bool $counter): TOTP|HOTP
    {
        if ($counter === false) {
            return TOTP::createFromSecret($key, FactoryImmutable::getDefaultInstance());
        }

        // counter based starts at the server's counter, so the app is in sync when scanning the QR code
        return HOTP::createFromSecret($key)->withCounter($counter === true ? 0 : max(0, $counter));
    }
}
