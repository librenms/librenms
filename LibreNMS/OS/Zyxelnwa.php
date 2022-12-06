<?php
/*
 * LibreNMS
 *
 * This program is free software: you can redistribute it and/or modify it
 * under the terms of the GNU General Public License as published by the
 * Free Software Foundation, either version 3 of the License, or (at your
 * option) any later version.  Please see LICENSE.txt at the top level of
 * the source code distribution for details.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2017 Thomas GAGNIERE
 * @author     Thomas GAGNIERE <tgagniere@reseau-concept.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\WirelessSensor;
use LibreNMS\Interfaces\Discovery\OSDiscovery;
use LibreNMS\Interfaces\Discovery\Sensors\WirelessClientsDiscovery;
use LibreNMS\OS\Shared\Zyxel;

class Zyxelnwa extends Zyxel implements OSDiscovery, WirelessClientsDiscovery
{
    
    public function discoverWirelessClients()
    {
        $sensors = [];
        $data = $this->getCacheTable('ZYXEL-ES-WIRELESS::wlanRadioTable');
        foreach ($data as $index => $entry) {
            $mode = '';
            switch ($entry['wlanMode']) {
              case 1:
                $mode = '2.4Ghz';
                break;
              case 2:
                $mode = '5Ghz';
                break;
              default:
                $mode = "Unknown";
            }


            $sensors[] = new WirelessSensor(
                'clients',
                $this->getDeviceId(),
                '.1.3.6.1.4.1.890.1.15.3.5.1.1.2.' . $index,
                'zyxelnwa',
                $index,
                "$mode",
                $entry['wlanStationCount']
            );
        }

        return $sensors;
    }

}
