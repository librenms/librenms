<?php
/**
 * Vrp.php
 *
 * Huawei VRP
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

namespace LibreNMS\OS;

use Illuminate\Support\Str;
use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\NacPolling;
use LibreNMS\OS;
use App\Models\PortsNac;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\OSDiscovery;

class Vrp extends OS implements
    ProcessorDiscovery,
    NacPolling,
    WirelessApCountDiscovery,
    WirelessClientsDiscovery,
    OSDiscovery
{

    /**
     * Discover OS Vrp
     *
     */
    public function discoverOS(): void
    {
        $device = $this->getDeviceModel();
        //Huawei VRP devices are not providing the HW description in a unified way
        preg_match("/Version [^\s]*/m", $device->sysDescr, $matches);
        $device->version = trim(str_replace('Version ', '', $matches[0]));

        preg_match("/\(([^\s]*) (V[0-9]{3}R[0-9]{3}[0-9A-Z]+)/m", $device->sysDescr, $matches);
        if (!empty($matches[2])) {
            $device->version .= " (" . trim($matches[2]) . ")";
        }

        $patch = snmp_getnext($this->getDevice(), 'HUAWEI-SYS-MAN-MIB::hwPatchVersion', '-OQv');
        if ($patch) {
            $device->version .= " [$patch]";
        }

        $oidList = [
            'HUAWEI-ENTITY-EXTENT-MIB::hwEntityExtentMIB.6.5.0',
            'HUAWEI-DEVICE-EXT-MIB::hwProductName.0',
            'HUAWEI-MIB::hwDatacomm.183.1.25.1.5.1',
            'HUAWEI-MIB::mlsr.20.1.1.1.3.0',
            'HUAWEI-ENTITY-EXTENT-MIB::hwEntityBoardName.9',
        ];

        foreach ($oidList as $oid) {
            $hardware_tmp = snmp_get($this->getDevice(), $oid, '-OQv');

            if (!empty($hardware_tmp)) {
                $device->hardware = "Huawei " . $hardware_tmp;
                break;
            }
        }

        // Let's use sysDescr if nothing else is found in the OIDs. sysDescr is less detailled than OIDs most of the time
        if (empty($hardware_tmp) && !empty($matches[1])) {
            $device->hardware = "Huawei " . trim($matches[1]);
        }

        // Serial
        // Multiple possible OIDs to grab the serial number

        $oidList = [
            'ENTITY-MIB::entPhysicalSerialNum.9',
        ];
        foreach ($oidList as $oid) {
            $serial_tmp = snmp_get($this->getDevice(), $oid, '-OQv');
            if (!empty($serial_tmp)) {
                $device->serial = $serial_tmp;
                break;
            }
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $device = $this->getDevice();

        $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityCpuUsage', array(), 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');

        if (!empty($processors_data)) {
            $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityMemSize', $processors_data, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
            $processors_data = snmpwalk_cache_multi_oid($device, 'hwEntityBomEnDesc', $processors_data, 'HUAWEI-ENTITY-EXTENT-MIB', 'huawei');
        }

        d_echo($processors_data);

        $processors = array();
        foreach ($processors_data as $index => $entry) {
            if ($entry['hwEntityMemSize'] != 0) {
                d_echo($index.' '.$entry['hwEntityBomEnDesc'].' -> '.$entry['hwEntityCpuUsage'].' -> '.$entry['hwEntityMemSize']."\n");

                $usage_oid = '.1.3.6.1.4.1.2011.5.25.31.1.1.1.1.5.'.$index;
                $descr = $entry['hwEntityBomEnDesc'];
                $usage = $entry['hwEntityCpuUsage'];

                if (empty($descr) || Str::contains($descr, 'No') || Str::contains($usage, 'No')) {
                    continue;
                }

                $processors[] = Processor::discover(
                    $this->getName(),
                    $this->getDeviceId(),
                    $usage_oid,
                    $index,
                    $descr,
                    1,
                    $usage
                );
            }
        }

        return $processors;
    }

    /**
    * Discover the Network Access Control informations (dot1X etc etc)
    *
    */
    public function pollNac()
    {
        $nac = collect();
        // We collect the first table
        $portAuthSessionEntry = snmpwalk_cache_oid($this->getDevice(), 'hwAccessTable', [], 'HUAWEI-AAA-MIB');

        if (!empty($portAuthSessionEntry)) {
            // If it is not empty, lets add the Extended table
            $portAuthSessionEntry = snmpwalk_cache_oid($this->getDevice(), 'hwAccessExtTable', $portAuthSessionEntry, 'HUAWEI-AAA-MIB');
            // We cache a port_ifName -> port_id map
            $ifName_map = $this->getDeviceModel()->ports()->pluck('port_id', 'ifName');

            // update the DB
            foreach ($portAuthSessionEntry as $authId => $portAuthSessionEntryParameters) {
                $mac_address = strtolower(implode(array_map('zeropad', explode(':', $portAuthSessionEntryParameters['hwAccessMACAddress']))));
                $port_id = $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'], 0);
                if ($port_id <=0) {
                    continue; //this would happen for an SSH session for instance
                }
                $nac->put($mac_address, new PortsNac([
                    'port_id' => $ifName_map->get($portAuthSessionEntryParameters['hwAccessInterface'], 0),
                    'mac_address' => $mac_address,
                    'auth_id' => $authId,
                    'domain' => $portAuthSessionEntryParameters['hwAccessDomain'],
                    'username' => ''.$portAuthSessionEntryParameters['hwAccessUserName'],
                    'ip_address' => $portAuthSessionEntryParameters['hwAccessIPAddress'],
                    'authz_by' => ''.$portAuthSessionEntryParameters['hwAccessType'],
                    'authz_status' => ''.$portAuthSessionEntryParameters['hwAccessAuthorizetype'],
                    'host_mode' => is_null($portAuthSessionEntryParameters['hwAccessAuthType'])?'default':$portAuthSessionEntryParameters['hwAccessAuthType'],
                    'timeout' => $portAuthSessionEntryParameters['hwAccessSessionTimeout'],
                    'time_elapsed' => $portAuthSessionEntryParameters['hwAccessOnlineTime'],
                    'authc_status' => $portAuthSessionEntryParameters['hwAccessCurAuthenPlace'],
                    'method' => ''.$portAuthSessionEntryParameters['hwAccessAuthtype'],
                    'vlan' => $portAuthSessionEntryParameters['hwAccessVLANID'],
                ]));
            }
        }
        return $nac;
    }

    public function discoverWirelessApCount()
    {
        $sensors = array();
        $ap_number = snmpwalk_cache_oid($this->getDevice(), 'hwWlanCurJointApNum.0', array(), 'HUAWEI-WLAN-GLOBAL-MIB');

        $sensors[] = new WirelessSensor(
            'ap-count',
            $this->getDeviceId(),
            '.1.3.6.1.4.1.2011.6.139.12.1.2.1.0',
            'vrp-ap-count',
            'ap-count',
            'AP Count',
            $ap_number[0]['hwWlanCurJointApNum']
        );
        return $sensors;
    }

    public function discoverWirelessClients()
    {
        $sensors = array();
        $total_oids = array();

        $vapInfoTable = $this->getCacheTable('hwWlanVapInfoTable', 'HUAWEI-WLAN-VAP-MIB', 3);
        
        foreach ($vapInfoTable as $a_index => $ap) {
            //Convert mac address (hh:hh:hh:hh:hh:hh) to dec OID (ddd.ddd.ddd.ddd.ddd.ddd)
            $a_index_oid = implode(".", array_map("hexdec", explode(":", $a_index)));
            foreach ($ap as $r_index => $radio) {
                foreach ($radio as $s_index => $ssid) {
                    $oid = '.1.3.6.1.4.1.2011.6.139.17.1.1.1.9.' . $a_index_oid . '.' . $r_index . '.' . $s_index ;
                    $total_oids[] = $oid;
                    $sensors[] = new WirelessSensor(
                        'clients',
                        $this->getDeviceId(),
                        $oid,
                        'vrp',
                        $a_index_oid . '.' . $r_index . '.' . $s_index,
                        'Radio:' . $r_index . ' SSID:' . $ssid['hwWlanVapProfileName'],
                        $ssid['hwWlanVapStaOnlineCnt']
                    );
                }
            }
        }
        return $sensors;
    }
}
