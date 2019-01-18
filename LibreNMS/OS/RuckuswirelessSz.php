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
        $oid = '.1.3.6.1.4.1.25053.1.4.1.1.1.15.2.0'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumSta.0
        return array(
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'ruckuswireless-sz', 1, 'Clients: Total')
        );
    }
    public function discoverWirelessApCount()
    {
        $oid = '.1.3.6.1.4.1.25053.1.8.1.1.1.1.3.1.9.139.32.129.213.150.98.64.217.163.219.42.60.244.221.227.247.247.122.136.22.48.73.64.205.132.132.130.145.146.117.221.195'; //RUCKUS-SZ-SYSTEM-MIB::ruckusSZSystemStatsNumAP.0
        return array(
            new WirelessSensor('ap-count', $this->getDeviceId(), $oid, 'ruckuswireless-sz', 1, 'Connected APs')
        );
    }
}
