<?php
/**
 * Text.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class StringHelpers
{
    /**
     * Shorten text over 50 chars, if shortened, add ellipsis
     *
     * @param string $string
     * @param int $max
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
            'pi-hole' => 'pi-hole',
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
     * @param string $string
     * @return string
     */
    public static function camelToTitle($string)
    {
        return ucwords(implode(' ', preg_split('/(?=[A-Z])/', $string)));
    }
}
