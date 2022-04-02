<?php
/**
 * StringHelpers.php
 *
 * -Description-
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
 * @link       https://www.librenms.org
 *
 * @copyright  2021 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class StringHelpers
{
    /**
     * Shorten text over 50 chars, if shortened, add ellipsis
     *
     * @param  string  $string
     * @param  int  $max
     * @return string
     */
    public static function shortenText($string, $max = 30)
    {
        if (strlen($string) > 50) {
            return substr($string, 0, $max) . '...';
        }

        return $string;
    }

    public static function niceCase($string)
    {
        $replacements = [
            'bind' => 'BIND',
            'dbm' => 'dBm',
            'dhcp-stats' => 'DHCP Stats',
            'entropy' => 'Random entropy',
            'exim-stats' => 'EXIM Stats',
            'fbsd-nfs-client' => 'FreeBSD NFS Client',
            'fbsd-nfs-server' => 'FreeBSD NFS Server',
            'freeradius' => 'FreeRADIUS',
            'gpsd' => 'GPSD',
            'mailcow-postfix' => 'mailcow-dockerized postfix',
            'mysql' => 'MySQL',
            'nfs-server' => 'NFS Server',
            'nfs-stats' => 'NFS Stats',
            'nfs-v3-stats' => 'NFS v3 Stats',
            'ntp' => 'NTP',
            'ntp-client' => 'NTP Client',
            'ntp-server' => 'NTP Server',
            'opengridscheduler' => 'Open Grid Scheduler',
            'os-updates' => 'OS Updates',
            'php-fpm' => 'PHP-FPM',
            'pi-hole' => 'Pi-hole',
            'powerdns' => 'PowerDNS',
            'powerdns-dnsdist' => 'PowerDNS dnsdist',
            'powerdns-recursor' => 'PowerDNS Recursor',
            'powermon' => 'PowerMon',
            'pureftpd' => 'PureFTPd',
            'rrdcached' => 'RRDCached',
            'sdfsinfo' => 'SDFS info',
            'smart' => 'SMART',
            'ups-apcups' => 'UPS apcups',
            'ups-nut' => 'UPS nut',
            'zfs' => 'ZFS',
        ];

        return isset($replacements[$string]) ? $replacements[$string] : ucwords(str_replace(['_', '-'], ' ', $string));
    }

    /**
     * Convert a camel or studly case string to Title case (with spaces)
     *
     * @param  string  $string
     * @return string
     */
    public static function camelToTitle($string)
    {
        return ucwords(implode(' ', preg_split('/(?=[A-Z])/', $string)));
    }

    /**
     * Sometimes devices store strings as non-unicode strings and return them directly.
     * NetSnmp parses those as UTF-8, try to convert the string if it contains non-printable ascii characters.
     *
     * @param  string|null  $string
     * @return string
     */
    public static function inferEncoding(?string $string): ?string
    {
        if (empty($string) || preg_match('//u', $string) || ! function_exists('iconv')) {
            return $string;
        }

        $charset = config('app.charset');

        if (($converted = @iconv($charset, 'UTF-8', $string)) !== false) {
            return (string) $converted;
        }

        if ($charset !== 'Windows-1252' && ($converted = @iconv('Windows-1252', 'UTF-8', $string)) !== false) {
            return (string) $converted;
        }

        if ($charset !== 'CP850' && ($converted = @iconv('CP850', 'UTF-8', $string)) !== false) {
            return (string) $converted;
        }

        \Log::debug('Failed to convert string: ' . $string);

        return $string;
    }

    /**
     * Generate a class name from a lowercase string containing - or _
     * Remove - and _ and camel case words
     *
     * @param  string  $name  The string to convert to a class name
     * @param  string|null  $namespace  namespace to prepend to the name for example: LibreNMS\
     * @return string Class name
     */
    public static function toClass(string $name, ?string $namespace = null): string
    {
        $pre_format = str_replace(['-', '_'], ' ', $name);
        $class = str_replace(' ', '', ucwords(strtolower($pre_format)));
        $class = preg_replace_callback('/^(\d)(.)/', function ($matches) {
            $numbers = ['Zero', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine'];

            return $numbers[$matches[1]] . strtoupper($matches[2]);
        }, $class);

        return $namespace . $class;
    }

    /**
     * Check if variable can be cast to a string
     *
     * @param  mixed  $var
     * @return bool
     */
    public static function isStringable($var): bool
    {
        return $var === null || is_scalar($var) || (is_object($var) && method_exists($var, '__toString'));
    }
}
