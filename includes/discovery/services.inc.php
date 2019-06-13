<?php
/**
 * services.inc.php
 *
 * Creates the correct handler for the trap and then sends it the trap.
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
 * @copyright  2019 KanREN, Inc.
 * @author     Heath Barnhart <hbarnhart@kanren.net>
 */

use LibreNMS\Config;

Class DiscoverService
{

    global $config;

    public function __construct()
    {
        //Walk tcpListenerProcess and udpEndpointProcess
        $oidsTcp = trim(snmp_walk($device, '.1.3.6.1.2.1.6.20.1.4', '-Osqn'));
        $oidsUdp = trim(snmp_walk($device, '.1.3.6.1.2.1.7.7.1.8', '-Osqn'));
        $oids = $oidsTcp . $oidsUdp;

        foreach (explode("\n", $oids) as $data) {
            $data = trim($data);
            if ($data) {
                list($oid, $tcpstatus) = explode(' ', $data);
                $split_oid = explode('.', $oid);

                //Skip discovery for protocols bound to localhost
                $ipVersion = $split_oid[12];
                if ($ipVersion == 4) {
                    $listenV4 = implode(".", [$split_oid[13], $split_oid[14], $split_oid[15], $split_oid[16]]);
                    if ($listenV4 == "127.0.0.1") {
                        continue;
                    }
                } else {
                    for ($i = 13, $arrayV6 = []; $i < 29; $i++) {
                        $arrayV6[] = $split_oid[$i];
                    }
                    $listenV6 = implode($arrayV6);
                    if ($listenV6 == "0000000000000001") {
                        continue;
                    }
                }

                //Determine layer4 protocol, don't add duplicates
                $proto = $split_oid[7];
                switch ($proto) {
                    case 6:
                        $protoName = 'tcp';
                        $port  = $split_oid[(count($split_oid) - 1)];
                        settype($port, 'integer');
                        $tcpServices[] = $port;
                        if (1 !== count(array_keys($tcpServices, $port))) {
                            continue 2;
                        }
                        break;
                    case 7:
                        $protoName = 'udp';
                        if ($ipVersion == 4) {
                            $port = $split_oid[17];
                        } else {
                            $port = $split_oid[29];
                        }
                        $udpServices[] = $port;
                        settype($port, 'integer');
                        if (1 !== count(array_keys($udpServices, $port))) {
                            continue 2;
                        }
                        break;
                }


                //Only run discovery for service if it exists in /etc/services,
                //is unique per protocol, and there is a pluggin or script for the service
                $service = getservbyport($port, $protoName);
                $check_pluggin = $config['nagios_plugins'] . "/check_" . $service;
                $check_script = $config['install_dir'].'/includes/services/check_'.strtolower($service).'.inc.php';
                if (($service) && (is_file($check_pluggin) || is_file($check_script)) && Self::serviceUnique($service, $device)) {
                    ServiceDB::addService($device, $service, "(Auto discovered) $service", $device->hostname);
                    Log::event('Autodiscovered service: type ' . mres($service), $device, 'service', 2);
                    echo '+';
                }
            }
        }
    }

    //Checks to see if service already exists for device.
    private static fuction serviceUnique($service, $device) {
        $unique = false;
        if (! dbFetchCell('SELECT COUNT(service_id) FROM `services` WHERE `service_type`= ? AND `device_id` = ?', array($service, $device['device_id']))) {
            $unique = true;
        }
        return $unique;
    }
}
