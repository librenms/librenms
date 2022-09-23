<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class Ruckuswireless extends OS implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function discoverWirelessClients()
    {

// Find Per SSID Client Count
        $sensors = [];
        $ssids = $this->getCacheByIndex('ruckusZDWLANSSID', 'RUCKUS-ZD-WLAN-MIB');
        $counts = $this->getCacheByIndex('ruckusZDWLANNumSta', 'RUCKUS-ZD-WLAN-MIB');

        $total_oids = [];
        $total = 0;
        $index = null;
        foreach ($counts as $index => $count) {
            $oid = '.1.3.6.1.4.1.25053.1.2.2.1.1.1.1.1.12.' . $index;
            $total_oids[] = $oid;
            $total += $count;

            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                $oid,
                'ruckuswireless',
                $index,
                'SSID: ' . $ssids[$index],
                $count
            );
        }

        // Do not get total client count if only 1 SSID
        if (count($total_oids) > 1) {
            // Find Total Client Count
            $oid = '.1.3.6.1.4.1.25053.1.2.1.1.1.15.2.0'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0
            array_push($sensors, new WirelessSensor('clients', $this->getDeviceId(), $oid, 'ruckuswireless', ($index + 1), 'System Total:'));
        }

        return $sensors;
    }

    // Find Total AP Count

    public function discoverWirelessApCount()
    {
        $oidconnected = '.1.3.6.1.4.1.25053.1.2.1.1.1.15.1.0'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumAP.0
        $oidtotal = '.1.3.6.1.4.1.25053.1.2.1.1.1.15.15.0'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumRegisteredAP.0
        $sensorindex = 0;
        $sensors[] = new WirelessSensor(
                    'ap-count',
                    $this->getDeviceId(),
                    $oidconnected,
                    'ruckuswireless',
                    ++$sensorindex,
                    'Connected APs'
                );

        array_push($sensors, new WirelessSensor('ap-count', $this->getDeviceId(), $oidtotal, 'ruckuswireless', ++$sensorindex, 'Total APs'));

        return $sensors;
    }
}
