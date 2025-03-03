<?php
/*
 * Fortiextender.php
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
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2024 CTNET B.V.
 * @author     Rudy Broersma <r.broersma@ctnet.nl>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrpDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRsrqDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessRssiDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessSinrDiscovery;
use LibreNMS\OS\Shared\Fortinet;

class Fortiextender extends Fortinet implements
    WirelessSinrDiscovery,
    WirelessRsrpDiscovery,
    WirelessRsrqDiscovery,
    WirelessRssiDiscovery
{
    public function discoverWirelessSinr()
    {
        $sinr_group = snmpwalk_group($this->getDeviceArray(), 'fextInfoModemStatusSINR', 'FORTINET-FORTIEXTENDER-MIB', 1);
        $oid = '.1.3.6.1.4.1.12356.121.21.3.1.1.28.';

        $sinr = [];
        foreach ($sinr_group as $key => $sinr_entry) {
            $sinr[] = new WirelessSensor('sinr', $this->getDeviceId(), $oid . $key, 'fortiextender', $key, 'Modem ' . $key);
        }

        return $sinr;
    }

    public function discoverWirelessRsrp()
    {
        $rsrp_group = snmpwalk_group($this->getDeviceArray(), 'fextInfoModemStatusSINR', 'FORTINET-FORTIEXTENDER-MIB', 1);
        $oid = '.1.3.6.1.4.1.12356.121.21.3.1.1.29.';

        $rsrp = [];
        foreach ($rsrp_group as $key => $rsrp_entry) {
            $rsrp[] = new WirelessSensor('rsrp', $this->getDeviceId(), $oid . $key, 'fortiextender', $key, 'Modem ' . $key);
        }

        return $rsrp;
    }

    public function discoverWirelessRsrq()
    {
        $rsrq_group = snmpwalk_group($this->getDeviceArray(), 'fextInfoModemStatusRSRQ', 'FORTINET-FORTIEXTENDER-MIB', 1);
        $oid = '.1.3.6.1.4.1.12356.121.21.3.1.1.30.';

        $rsrq = [];
        foreach ($rsrq_group as $key => $rsrq_entry) {
            $rsrq[] = new WirelessSensor('rsrq', $this->getDeviceId(), $oid . $key, 'fortiextender', $key, 'Modem ' . $key);
        }

        return $rsrq;
    }

    public function discoverWirelessRssi()
    {
        $rsrq_group = snmpwalk_group($this->getDeviceArray(), 'fextInfoModemStatusRSSI', 'FORTINET-FORTIEXTENDER-MIB', 1);
        $oid = '.1.3.6.1.4.1.12356.121.21.3.1.1.22.';

        $rsrq = [];
        foreach ($rsrq_group as $key => $rsrq_entry) {
            $rsrq[] = new WirelessSensor('rssi', $this->getDeviceId(), $oid . $key, 'fortiextender', $key, 'Modem ' . $key);
        }

        return $rsrq;
    }
}
