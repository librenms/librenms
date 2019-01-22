<?php
/**
 * Applications.php
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

namespace App\Models;

class Application extends DeviceRelatedModel
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The primary key column name.
     *
     * @var string
     */
    protected $primaryKey = 'app_id';

    protected static $display_name = [
        'bind' => 'BIND',
        'dhcp-stats' => 'DHCP Stats',
        'exim-stats' => 'EXIM Stats',
        'fbsd-nfs-client' => 'FreeBSD NFS Client',
        'fbsd-nfs-server' => 'FreeBSD NFS Server',
        'freeradius' => 'FreeRADIUS',
        'gpsd' => 'GPSD',
        'mysql' => 'MySQL',
        'nfs-stats' => 'NFS Stats',
        'nfs-v3-stats' => 'NFS v3 Stats',
        'nfs-server' => 'NFS Server',
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
        'sdfsinfo' => 'SDFS info',
        'smart' => 'SMART',
        'ups-apcups' => 'UPS apcups',
        'ups-nut' => 'UPS nut',
        'zfs' => 'ZFS',
    ];

    // ---- Helper Functions ----

    public function displayName()
    {
        return collect(self::$display_name)
            ->get($this->app_type, ucwords(str_replace(['_', '-'], ' ', $this->app_type)));
    }
}
