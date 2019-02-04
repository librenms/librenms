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

// Total AP Count

      public function discoverWirelessApCount()
      {
        $apconnected = $this->getCacheByIndex('ruckusCtrlSystemNodeNumApConnected', '-Ob', 'RUCKUS-CTRL-MIB');

        foreach ($apconnected as $index => $connect) {
            $oid = '.1.3.6.1.4.1.25053.1.8.1.1.1.1.1.1.19.' . $index;

            $apstatus[] = new WirelessSensor(
                'ap-count',
                $this->getDeviceId(),
                $oid,
                'ruckuswireless-sz',
                $index,
                'Connected APs',
                $connect
          );
      }

        $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.1.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
        array_push($apstatus, new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'ruckuswireless-sz', ($index + 1), 'Total APs'));
        return $apstatus;
    }
}
