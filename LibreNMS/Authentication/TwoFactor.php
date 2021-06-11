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
 * @link       https://www.librenms.org
 * @author f0o <f0o@devilcode.org>
 * @copyright 2014 f0o, LibreNMS
 * @copyright  2017 Tony Murray
 */

namespace LibreNMS\Authentication;

class TwoFactor
{
    /**
     * Key Interval in seconds.
     * Set to 30s due to Google-Authenticator limitation.
     * Sadly Google-Auth is the most used Non-Physical OTP app.
     */
    const KEY_INTERVAL = 30;

    /**
     * Size of the OTP.
     * Set to 6 for the same reasons as above.
     */
    const OTP_SIZE = 6;

    /**
     * Window to honour whilest verifying OTP.
     */
    const OTP_WINDOW = 4;

    /**
     * Base32 Decoding dictionary
     */
    private static $base32 = [
        'A' => 0,
        'B' => 1,
        'C' => 2,
        'D' => 3,
        'E' => 4,
        'F' => 5,
        'G' => 6,
        'H' => 7,
        'I' => 8,
        'J' => 9,
        'K' => 10,
        'L' => 11,
        'M' => 12,
        'N' => 13,
        'O' => 14,
        'P' => 15,
        'Q' => 16,
        'R' => 17,
        'S' => 18,
        'T' => 19,
        'U' => 20,
        'V' => 21,
        'W' => 22,
        'X' => 23,
        'Y' => 24,
        'Z' => 25,
        '2' => 26,
        '3' => 27,
        '4' => 28,
        '5' => 29,
        '6' => 30,
        '7' => 31,
    ];

    /**
     * Base32 Encoding dictionary
     */
    private static $base32_enc = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';

    /**
     * Generate Secret Key
     * @return string
     */
    public static function genKey()
    {
        // RFC 4226 recommends 160bits Secret Keys, that's 20 Bytes for the lazy ones.
        $crypto = false;
        $raw = '';
        $x = -1;
        while ($crypto == false || ++$x < 10) {
            $raw = openssl_random_pseudo_bytes(20, $crypto);
        }
        // RFC 4648 Base32 Encoding without padding
        $len = strlen($raw);
        $bin = '';
        $x = -1;
        while (++$x < $len) {
            $bin .= str_pad(base_convert(ord($raw[$x]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $bin = str_split($bin, 5);
        $ret = '';
        $x = -1;
        while (++$x < sizeof($bin)) {
            $ret .= self::$base32_enc[base_convert(str_pad($bin[$x], 5, '0'), 2, 10)];
        }

        return $ret;
    }

    /**
     * Verify HOTP token honouring window
     *
     * @param string $key Secret Key
     * @param int $otp OTP supplied by user
     * @param int|bool $counter Counter, if false timestamp is used
     * @return bool|int
     */
    public static function verifyHOTP($key, $otp, $counter = false)
    {
        if (self::oathHOTP($key, $counter) == $otp) {
            return true;
        } else {
            if ($counter === false) {
                //TimeBased HOTP requires lookbehind and lookahead.
                $counter = floor(microtime(true) / self::KEY_INTERVAL);
                $initcount = $counter - ((self::OTP_WINDOW + 1) * self::KEY_INTERVAL);
                $endcount = $counter + (self::OTP_WINDOW * self::KEY_INTERVAL);
                $totp = true;
            } else {
                //Counter based HOTP only has lookahead, not lookbehind.
                $initcount = $counter - 1;
                $endcount = $counter + self::OTP_WINDOW;
                $totp = false;
            }
            while (++$initcount <= $endcount) {
                if (self::oathHOTP($key, $initcount) == $otp) {
                    if (! $totp) {
                        return $initcount;
                    } else {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Generate HOTP (RFC 4226)
     * @param string $key Secret Key
     * @param int|bool $counter Optional Counter, Defaults to Timestamp
     * @return string
     */
    private static function oathHOTP($key, $counter = false)
    {
        if ($counter === false) {
            $counter = floor(microtime(true) / self::KEY_INTERVAL);
        }

        $length = strlen($key);
        $x = -1;
        $y = $z = 0;
        $kbin = '';
        while (++$x < $length) {
            $y <<= 5;
            $y += self::$base32[$key[$x]];
            $z += 5;
            if ($z >= 8) {
                $z -= 8;
                $kbin .= chr(($y & (0xFF << $z)) >> $z);
            }
        }
        $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $counter), $kbin, true);
        $offset = ord($hash[19]) & 0xf;
        $truncated = (((ord($hash[$offset + 0]) & 0x7f) << 24) |
                ((ord($hash[$offset + 1]) & 0xff) << 16) |
                ((ord($hash[$offset + 2]) & 0xff) << 8) |
                (ord($hash[$offset + 3]) & 0xff)) % pow(10, self::OTP_SIZE);

        return str_pad($truncated, self::OTP_SIZE, '0', STR_PAD_LEFT);
    }

    /**
     * Generate 2fa URI
     * @param string $username
     * @param string $key
     * @param bool $counter if type is counter (false for time based)
     * @return string
     */
    public static function generateUri($username, $key, $counter = false)
    {
        $title = 'LibreNMS:' . urlencode($username);

        return $counter ?
            "otpauth://hotp/$title?issuer=LibreNMS&counter=1&secret=$key" : // counter based
            "otpauth://totp/$title?issuer=LibreNMS&secret=$key"; // time based
    }
}
