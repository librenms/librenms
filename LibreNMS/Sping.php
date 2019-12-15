<?php
/**
 * Sping.php
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

namespace LibreNMS;

use App\Models\Device;
use Log;

class Sping
{
    private $oid;
    private $timeout;
    private $retries;

    /**
     * Create a new SNMP ping instance.
     */
    public function __construct()
    {
        $this->oid = "1.3.6.1.2.1.1.1.0";
        $this->timeout = (Config::get('snmp_ping_timeout', 5)) * 1000000;
        $this->retries = Config::get('snmp_ping_retries', 2);
    }

    /**
     * Run sping against a device and collect stats.
     *
     * @param object $device
     * @return object
     */
    public function sping($device)
    {
        $response = [
            'result' => (boolean)false,
            'last_ping_timetaken' => (float)0.0,
            'db' => [
                'xmt' => (int)1,
                'rcv' => (int)0,
                'loss' => (float)100.0,
                'min' => (float)0.0,
                'max' => (float)0.0,
                'avg' =>(float)0.0
            ]
        ];

        $version = $device['snmpver'];
        $target = Device::pollerTarget($device['hostname']);

        $rtt = (boolean)false;
        if (strpos($version, '2c') !== false) {
            $rtt = $this->sping2c($device, $target);
        } elseif (strpos($version, '3') !== false) {
            $rtt = $this->sping3($device, $target);
        } else { // version 1
            $rtt = $this->sping1($device, $target);
        }

        if ($rtt !== false) {
            $rtt = (Config::get('record_snmp_ping_rtt') === true ? (float)$rtt : (float)0.0);
            $response['result'] = (boolean)true;
            $response['last_ping_timetaken'] = $rtt;
            $response['db']['rcv'] = (int)1;
            $response['db']['loss'] = (float)0.0;
            $response['db']['min'] = $rtt;
            $response['db']['max'] = $rtt;
            $response['db']['avg'] = $rtt;
        }

        return $response;
    }

    private function sping1($device, $target)
    {
        $rtt = (boolean)false;
        $community = $device['community'];
        $before = microtime(true);
        $sysDescr = snmpget($target, $community, $this->oid, $this->timeout, $this->retries);
        $after = microtime(true);
        if ($sysDescr !== false) {
            $rtt = (float)(($after - $before) * 1000.0);
        }
        return $rtt;
    }

    private function sping2c($device, $target)
    {
        $rtt = (boolean)false;
        $community = $device['community'];
        $before = microtime(true);
        $sysDescr = snmp2_get($target, $community, $this->oid, $this->timeout, $this->retries);
        $after = microtime(true);
        if ($sysDescr !== false) {
            $rtt = (float)(($after - $before) * 1000.0);
        }
        return $rtt;
    }

    private function sping3($device, $target)
    {
        $rtt = (boolean)false;
        $authname = $device['authname'];
        $authlevel = $device['authlevel'];
        $authalgo = null;
        $authpass = null;
        $cryptalgo = null;
        $cryptpass = null;
        if ($authlevel === "authPriv") {
            $authalgo = $device['authalgo'];
            $authpass = $device['authpass'];
            $cryptalgo = $device['cryptoalgo'];
            $cryptpass = $device['cryptopass'];
        } elseif ($authlevel === "authNoPriv") {
            $authalgo = $device['authalgo'];
            $authpass = $device['authpass'];
        }
        $before = microtime(true);
        $sysDescr = snmp3_get($target, $authname, $authlevel, $authalgo, $authpass, $cryptalgo, $cryptpass, $this->oid, $this->timeout, $this->retries);
        $after = microtime(true);
        if ($sysDescr !== false) {
            $rtt = (float)(($after - $before) * 1000.0);
        }
        return $rtt;
    }
}
