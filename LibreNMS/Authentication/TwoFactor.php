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

use App\Facades\LibrenmsConfig;
use App\Models\User;
use Carbon\FactoryImmutable;
use OTPHP\HOTP;
use OTPHP\TOTP;

class TwoFactor
{
    /**
     * Time based leeway in seconds, accepts the codes valid this long before and after now.
     * Must be less than the 30 second period.
     */
    const TOTP_LEEWAY = 29;

    /**
     * Counter based lookahead, the user's counter may be ahead of the server's.
     */
    const HOTP_WINDOW = 4;

    /**
     * Secret size in bytes, 160 bits as recommended by RFC 4226.
     * The otphp default (64) does not fit in users_prefs.value with the other settings.
     */
    const SECRET_SIZE = 20;

    /**
     * New two-factor settings with a generated secret
     *
     * @return array{key: string, fails: int, last: int, counter: int|false}
     */
    public static function create(bool $counterBased): array
    {
        $otp = $counterBased
            ? HOTP::generate(self::SECRET_SIZE)
            : TOTP::generate(FactoryImmutable::getDefaultInstance(), self::SECRET_SIZE);

        return [
            'key' => $otp->getSecret(),
            'fails' => 0,
            'last' => 0,
            'counter' => $otp instanceof HOTP ? $otp->getCounter() : false,
        ];
    }

    /**
     * Verify a time based (TOTP) or counter based (HOTP) token
     *
     * @param  string  $key  Secret Key
     * @param  string  $otp  OTP supplied by user
     * @param  int|false  $counter  the stored counter, false for time based
     * @return bool|int false if invalid, true if a valid time based token, the next counter if a valid counter based token
     */
    public static function verify(string $key, string $otp, int|false $counter = false): bool|int
    {
        $otphp = self::otp($key, $counter);

        if ($otphp instanceof TOTP) {
            return $otphp->verify($otp, null, self::TOTP_LEEWAY);
        }

        // verify() moves the counter past the matched one
        return $otphp->verify($otp, null, self::HOTP_WINDOW) ? $otphp->getCounter() : false;
    }

    /**
     * Generate the 2fa URI for the QR code
     *
     * @param  int|false  $counter  the stored counter, false for time based
     */
    public static function generateUri(User $user, string $key, int|false $counter = false): string
    {
        // the label and issuer are only shown in the authenticator app, they may not contain a colon
        return self::otp($key, $counter)
            ->withLabel(str_replace(':', '', $user->email ?: $user->username))
            ->withIssuer(str_replace(':', '', LibrenmsConfig::get('project_name')) ?: 'LibreNMS')
            ->getProvisioningUri();
    }

    /**
     * @param  int|false  $counter  the stored counter, false for time based
     */
    private static function otp(string $key, int|false $counter): TOTP|HOTP
    {
        if ($counter === false) {
            return TOTP::createFromSecret($key, FactoryImmutable::getDefaultInstance());
        }

        // counter based starts at the server's counter, so the app is in sync when scanning the QR code
        return HOTP::createFromSecret($key)->withCounter($counter);
    }
}
