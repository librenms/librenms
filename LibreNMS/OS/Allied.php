<?php
/**
 * Allied.php
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
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\OS;

class Allied extends OS implements OSDiscovery
{
    public function discoverOS(Device $device): void
    {
        //OS: AT-S39
        //Legacy products: at8024, at8024GB, at8024M, at8016F, at8026FC
        $data = snmp_get_multi_oid($this->getDeviceArray(), ['atiswitchProductType.0', 'atiswitchSwVersion.0', 'atiswitchSw.0'], '-OsvQU', 'AtiSwitch-MIB');

        $hardware = $data['atiswitchProductType.0'];
        $version = $data['atiswitchSwVersion.0'];
        $software = $data['atiswitchSw.0'];

        if ($software && $version) {
            $version = $software . ' ' . $version;
        }

        //OS: AT-S41
        // sysDescr.0 = STRING: "AT-8126XL, AT-S21 version 1.4.2"
        // AtiL2-MIB::atiL2SwProduct.0 = STRING: "AT-8326GB"
        // AtiL2-MIB::atiL2SwVersion.0 = STRING: "AT-S41 v1.1.6 "
        if (! $hardware && ! $version && ! $software) {
            $hardware = snmp_get($this->getDeviceArray(), 'atiL2SwProduct.0', '-OsvQU', 'AtiL2-MIB');
            $version = snmp_get($this->getDeviceArray(), 'atiL2SwVersion.0', '-OsvQU', 'AtiL2-MIB');
        }

        //Alliedware Plus 2.x.x.x | Legacy products: 8100S
        //SNMPv2-MIB::sysDescr.0 = STRING: AlliedWare Plus (TM) 2.2.3.0
        if (! $hardware && ! $version) {
            $data = snmp_get_multi_oid($this->getDeviceArray(), ['.1.3.6.1.4.1.207.8.17.1.3.1.6.1', '.1.3.6.1.4.1.207.8.17.1.3.1.5.1', '.1.3.6.1.4.1.207.8.17.1.3.1.8.1']);

            $hardware = $data['.1.3.6.1.4.1.207.8.17.1.3.1.6.1'];
            $version = $data['.1.3.6.1.4.1.207.8.17.1.3.1.5.1'];
            $serial = $data['.1.3.6.1.4.1.207.8.17.1.3.1.8.1'];
        }

        //Gets OS outputting "Alliedware Plus" instead of just Alliedware.
        if ($hardware && $version) {
            $version = 'Plus ' . $version;
        }

        /*Products running Alliedware OS
          sysDescr.0 = STRING: "Allied Telesyn AT-8948 version 2.7.4-02 22-Aug-2005"
          sysDescr.0 = STRING: "Allied Telesis AT-8624T/2M version 2.9.1-13 11-Dec-2007"
        Use sysDescr to get Hardware, SW version, and Serial*/
        [$a, $b, $c, $d, $e, $f] = explode(' ', $this->getDeviceArray()['sysDescr']);
        if (! $hardware && ! $version) {
            if ($a == 'Allied' && $d == 'version') {
                $version = $e;
                $features = $f;
                $hardware = $c;
                $serial = snmp_get($this->getDeviceArray(), 'arBoardSerialNumber.1', '-OsvQU', 'AT-INTERFACES-MIB');

            //  sysDescr.0 = STRING: "CentreCOM 9924Ts, version 3.2.1-04, built 08-Sep-2009"
            } elseif ($a == 'CentreCOM' && $c == 'version') {
                $version = $d;
                $features = $f;
                $hardware = snmp_get($this->getDeviceArray(), 'arBoardName.1', '-OsvQU', 'AT-INTERFACES-MIB');
                $serial = snmp_get($this->getDeviceArray(), 'arBoardSerialNumber.1', '-OsvQU', 'AT-INTERFACES-MIB');

            //AT-GS950/24 Gigabit Ethernet WebSmart Switch
                //Also requires system description as no OIDs provide $hardware
            } elseif ($d == 'WebSmart' && $e == 'Switch') {
                $version = snmp_get($this->getDeviceArray(), 'swhub.167.81.1.3.0', '-OsvQU', 'AtiL2-MIB');
                $version = $d . ' ' . $version;
                $hardware = $a;
            }
        }
        // sysDescr.0 = STRING: "Allied Telesyn Ethernet Switch AT-8012M"
        if ($a == 'Allied' && $d == 'Switch') {
            $hardware = $e;
        }

        $device->version = str_replace(['"', ','], '', $version ?? null);
        $device->features = str_replace('"', '', $features ?? null);
        $device->hardware = str_replace('"', '', $hardware ?? null);
        $device->serial = $serial ?? null;
    }
}
