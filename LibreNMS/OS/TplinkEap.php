<?php
/**
 * TplinkEap.php
 *
 * TP linke EAP (Omada)
 *
 * @link       https://www.librenms.org
 *
 * @copyright  
 * @author     
 */

namespace LibreNMS\OS;

use App\Models\Device;
use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS;

class TplinkEap extends OS implements OSDiscovery,WirelessClientsDiscovery
{
   /**
    * 
    */ 
    public function discoverOS(Device $device): void
    {
        $device->hardware = snmp_get($this->getDeviceArray(), '.1.3.6.1.2.1.1.5.0', '-Osqnv');
        $device->version  = snmp_get($this->getDeviceArray(), '.1.3.6.1.2.1.1.1.0', '-Osqnv') ?: null;
    }

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
