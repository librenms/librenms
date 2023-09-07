<?php
/**
 * TplinkEap.php
 *
 * TP linke EAP (Omada)
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2023 st4ro
 * @author     st4ro <radek@starky.eu>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class TplinkEap extends OS implements WirelessClientsDiscovery
{
    /**
     * Discover wireless client counts. Type is clients.
     * Returns an array of LibreNMS\Device\Sensor objects that have been discovered
     *
     * @return array Sensors
     */
    public function discoverWirelessClients()
    {
        $oid = '.1.3.6.1.4.1.11863.10.1.1.1.0';

        return [
            new WirelessSensor('clients', $this->getDeviceId(), $oid, 'tplink-eap', 1, 'Clients'),
        ];
    }
}
