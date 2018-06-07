<?php
namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\Interfaces\Polling\Sensors\WirelessClientsPolling;
use LibreNMS\OS;

class AlliedWireless extends OS implements
    WirelessClientsDiscovery,
    WirelessClientsPolling
{
    public function discoverWirelessClients()
    {

        $sensors = array();

        $client_list =  snmpwalk_group($this->getDevice(), 'atkkWiAcClientAddress', array(), 'ATKK-WLAN-ACCESS');
        var_dump($client_list);
        $client_count = count($client_list);
        d_echo("Total Clients: " . $client_count . "\n\n");

            $oid = '.1.3.6.1.4.1.207.8.42.6.2.1.1.1.'; //ATKK-WLAN-ACCESS::atkkWiAcClientAddress

            $sensors[] = new WirelessSensor(
                    'clients',
                    $this->getDeviceId(),
                    $oid,
                    'AlliedWireless',
                    'atkkWiAcClientAddress',
                    'Clients: Total',
                    $client_count
            );
        return $sensors;
    }
    /**
     * Poll wireless frequency as MHz
     * The returned array should be sensor_id => value pairs
     *
     * @param array $sensors Array of sensors needed to be polled
     * @return array of polled data
     */

    public function pollWirelessClients(array $sensors)
    {
        $client_list =  snmpwalk_group($this->getDevice(), 'atkkWiAcClientAddress', array(), 'ATKK-WLAN-ACCESS');
        $client_count = count($client_list);
        $sensors[0][sensor_current] = $client_count;

        d_echo("Total Clients: " . $client_count . "\n\n");
        return $sensors;
    }
}



/*    public function discoverWirelessClients()
    {
//        $ifNames = $this->getCacheByIndex('ifName', 'IF-MIB');

        $sensors = array();

        $clienttable = snmpwalk_group($this->getDevice(), 'atkkWiAcClientTable','ATKK-WLAN-ACCESS');
        foreach ($clienttable as $index => $data) {

            $hex = explode(':', $index);
            $mac = implode(array_map('hexdec', $hex), '.');

            d_echo($clienttable[$index]['atkkWiAcClientSSID']. " HERE \n\n");
            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.207.8.42.6.2.1.1.1.' . $mac,
                'AlliedWireless',
                $index,
                $clienttable[$index]['atkkWiAcClientSSID'] . " Clients: Total",
                $data['atkkWiAcClientSSID']
            );
        }

        return $sensors;
    }
}
*/