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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;

class StringHelpers
{
    /**
     * Shorten text over 50 chars, if shortened, add ellipsis
     *
     * @param $string
     * @param int $max
     * @return string
     */
    public static function shortenText($string, $max = 30)
    {
        if (strlen($string) > 50) {
            return substr($string, 0, $max) . "...";
        }

        return $string;
    }

    public static function niceCase($string)
    {
        $replacements = [
            'dbm' => 'dBm',
            'entropy' => 'Random entropy',
            'mysql' => 'MySQL',
            'powerdns' => 'PowerDNS',
            'bind' => 'BIND',
            'nfs-stats' => 'NFS Stats',
            'nfs-v3-stats' => 'NFS v3 Stats',
            'nfs-server' => 'NFS Server',
            'ntp' => 'NTP',
            'ntp-client' => 'NTP Client',
            'ntp-server' => 'NTP Server',
            'os-updates' => 'OS Updates',
            'smart' => 'SMART',
            'powerdns-recursor' => 'PowerDNS Recursor',
            'powerdns-dnsdist' => 'PowerDNS dnsdist',
            'dhcp-stats' => 'DHCP Stats',
            'ups-nut' => 'UPS nut',
            'ups-apcups' => 'UPS apcups',
            'gpsd' => 'GPSD',
            'exim-stats' => 'EXIM Stats',
            'fbsd-nfs-client' => 'FreeBSD NFS Client',
            'fbsd-nfs-server' => 'FreeBSD NFS Server',
            'php-fpm' => 'PHP-FPM',
            'opengridscheduler' => 'Open Grid Scheduler',
            'sdfsinfo' => 'SDFS info',
            'freeradius' => 'FreeRADIUS',
            'pi-hole' => 'pi-hole',
            'zfs' => 'ZFS',
        ];

        return isset($replacements[$string]) ? $replacements[$string] : ucwords(str_replace(['_', '-'], ' ', $string));
    }

    /**
     * Convert a camel or studly case string to Title case (with spaces)
     * @param $string
     * @return string
     */
    public static function camelToTitle($string)
    {
        return ucwords(implode(' ', preg_split('/(?=[A-Z])/', $string)));
    }
}
