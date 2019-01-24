<?php
namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessApCountDiscovery;
use LibreNMS\OS;

class RuckuswirelessSz extends OS implements
    WirelessClientsDiscovery,
    WirelessApCountDiscovery
{
    public function discoverWirelessClients()
   {

// Find Per SSID Client Count
        $sensors = array();
        $ssids = $this->getCacheByIndex('ruckusSZWLANSSID', 'RUCKUS-SZ-WLAN-MIB');
        $counts = $this->getCacheByIndex('ruckusSZWLANNumSta', 'RUCKUS-SZ-WLAN-MIB');

        $total_oids = array();
        $total = 0;
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

// Find Total Client Count

        $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.2.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumSta.0
        array_push($sensors, new WirelessSensor('clients', $this->getDeviceId(), $oid, 'ruckuswireless-sz', ($index + 1), 'System Total:'));
        return $sensors;
   }

// Find Total AP Count

    public function discoverWirelessApCount()
    {
        $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
        return array(
            new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'ruckuswireless-sz', 1, 'Connected APs')
        );
    }
}
