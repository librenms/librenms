<?php

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class RuckuswirelessSz extends OS implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function discoverOS(Device $device): void
    {
        $device->hardware = snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.3', '-OQv');
        $device->version = snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.9', '-OQv');
        $device->serial = snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.4.1.1.1.15.13.0', '-OQv');
        $device->features = 'Licenses: ' . snmp_get($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0', '-OQv') . '/' . snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.10', '-OQv');

        $ruckuscountry = snmp_getnext($this->getDeviceArray(), '.1.3.6.1.4.1.25053.1.8.1.1.1.1.3.1.4', '-OQv');
        if (! empty($ruckuscountry)) {
            $device->version .= " ($ruckuscountry)";
        }
    }

    public function discoverWirelessClients()
    {
        // clients - Discover Per SSID Client Count
        $sensors = [];
        $ssids = $this->getCacheByIndex('ruckusSZWLANSSID', 'RUCKUS-SZ-WLAN-MIB');
        $counts = $this->getCacheByIndex('ruckusSZWLANNumSta', 'RUCKUS-SZ-WLAN-MIB');

        $total_oids = [];
        $total = 0;
        $index = null;
        foreach ($counts as $index => $count) {
            $oid = '.1.3.6.1.4.1.25053.1.4.2.1.1.1.2.1.12.' . $index;
            $total_oids[] = $oid;
            $total += $count;

            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $oid,
                'ruckuswireless-sz',
                $index,
                'SSID: ' . $ssids[$index],
                $count
            );
        }

        // Do not get total client count if only 1 SSID
        if (count($total_oids) > 1) {
            // clients - Discover System Total Client Count
            $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.2.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumSta.0
            array_push($sensors, new WirelessSensor('clients', $this->getDeviceId(), $oid, 'ruckuswireless-sz', ($index + 1), 'System Total:'));
        }

        return $sensors;
    }

    // ap-count - Discover System Connected APs

    public function discoverWirelessApCount()
    {
        $apconnected = $this->getCacheByIndex('ruckusCtrlSystemNodeNumApConnected', 'RUCKUS-CTRL-MIB', '-OQUsb');
        $dbindex = 0;
        $apstatus = [];
        foreach ($apconnected as $index => $connect) {
            $oid = '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.19.' . $index;
            $apstatus[] = new WirelessSensor(
                'ap-count',
                $this->getDeviceId(),
                $oid,
                'ruckuswireless-sz',
                ++$dbindex,
                'Connected APs',
                $connect
            );
        }
        // ap-count - Discover System Total APs
        $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
        array_push($apstatus, new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'ruckuswireless-sz', ++$dbindex, 'Total APs'));

        return $apstatus;
    }
}
