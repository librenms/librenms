<?php

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessErrorsDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessNoiseFloorDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessUtilizationDiscovery;
use LibreNMS\OS;

class RuckuswirelessHotzone extends OS implements
    WirelessClientsDiscovery,
    WirelessUtilizationDiscovery,
    WirelessNoiseFloorDiscovery,
    WirelessErrorsDiscovery
{
    public function discoverWirelessClients()
    {
        $clients_2 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.2.1'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0
        $clients_5 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.2.2'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $clients_2, 'ruckuswireless-hotzone', 1, 'Clients: 2.4G'),
            new WirelessSensor('clients', $this->getDeviceId(), $clients_5, 'ruckuswireless-hotzone', 2, 'Clients: 5G'),
        ];
    }

    public function discoverWirelessUtilization()
    {
        $utilization_2 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.50.1'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0
        $utilization_5 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.50.2'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0

        return [
            new WirelessSensor('utilization', $this->getDeviceId(), $utilization_2, 'ruckuswireless-hotzone', 1, 'Utilization: 2.4G'),
            new WirelessSensor('utilization', $this->getDeviceId(), $utilization_5, 'ruckuswireless-hotzone', 2, 'Utilization: 5G'),
        ];
    }

    public function discoverWirelessNoiseFloor()
    {
        $noise_floor_2 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.2.1.8.1'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0
        $noise_floor_5 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.2.1.8.2'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0

        return [
            new WirelessSensor('noise-floor', $this->getDeviceId(), $noise_floor_2, 'ruckuswireless-hotzone', 1, 'Noise-floor: 2.4G'),
            new WirelessSensor('noise-floor', $this->getDeviceId(), $noise_floor_5, 'ruckuswireless-hotzone', 2, 'Noise-floor: 5G'),
        ];
    }

    public function discoverWirelessErrors()
    {
        $errors_2 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.21.1'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0
        $errors_5 = '.1.3.6.1.4.1.25053.1.1.12.1.1.1.3.1.21.2'; //RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatsNumSta.0

        return [
            new WirelessSensor('errors', $this->getDeviceId(), $errors_2, 'ruckuswireless-hotzone', 1, 'Received errors: 2.4G'),
            new WirelessSensor('errors', $this->getDeviceId(), $errors_5, 'ruckuswireless-hotzone', 2, 'Received errors: 5G'),
        ];
    }
}
