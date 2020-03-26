<?php
/**
 * DeviceHelper.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Util;


use App\Models\Device;
use LibreNMS\Config;
use LibreNMS\Net\Fping;
use LibreNMS\SNMP;
use Log;

class DeviceHelper
{
    private $device;

    /**
     * DeviceHelper constructor.
     * @param Device $device
     */
    public function __construct($device)
    {
        $this->device = $device;
    }

    public function canPing() {
        return Config::get('icmp_check') &&
            !($this->device->getAttrib('override_icmp_disable') && $this->device->getAttrib('override_icmp_disable') == "true");
    }

    /**
     * Check if the given host responds to ICMP echo requests ("pings").
     *
     * @param string $address_family The address family ('ipv4' or 'ipv6') to use. Defaults to IPv4.
     * Will *not* be autodetected for IP addresses, so it has to be set to 'ipv6' when pinging an IPv6 address or an IPv6-only host.
     *
     * @return array  'result' => bool pingable, 'last_ping_timetaken' => int time for last ping, 'db' => fping results
     */
    public function checkPing($address_family = 'ipv4')
    {
        if (!$this->canPing()) {
            return [
                'result' => true,
                'last_ping_timetaken' => 0
            ];
        }

        $fping = new Fping($this->device->hostname);
        $status = $fping->ping(
            Config::get('fping_options.count', 3),
            Config::get('fping_options.interval', 500),
            Config::get('fping_options.timeout', 500),
            $address_family
        );

        if ($status['dup'] > 0) {
            Log::event('Duplicate ICMP response detected! This could indicate a network issue.', getidbyname($this->device->hostname), 'icmp', 4);
            $status['exitcode'] = 0;   // when duplicate is detected fping returns 1. The device is up, but there is another issue. Clue admins in with above event.
        }

        return [
            'result' => ($status['exitcode'] == 0 && $status['loss'] < 100),
            'last_ping_timetaken' => $status['avg'],
            'db' => array_intersect_key($status, array_flip(['xmt','rcv','loss','min','max','avg']))
        ];
    }

    public function checkSNMP()
    {
        $snmp = new SNMP;

        $time_start = microtime(true);

        $oid = '.1.3.6.1.2.1.1.2.0';
        $options = '-Oqvn';
        $cmd = $snmp->genSnmpgetCmd($this->device, $oid, $options);
        exec($cmd, $data, $code);
        Log::debug("SNMP Check response code: $code");

        $snmp->recordSnmpStatistic('snmpget', $time_start);

        if ($code === 0) {
            return true;
        }

        $output = SNMP::get($this->device, "sysObjectID.0", "-Oqv", "SNMPv2-MIB");
        return !empty($output);
    }
}