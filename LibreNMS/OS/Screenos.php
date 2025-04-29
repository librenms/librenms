<?php

/*
 * Screenos.php
 *
 * -Description-
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @package    LibreNMS
 * @link       https://www.librenms.org
 * @copyright  2020 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv4Mac;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Discovery\ArpTableDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;
use LibreNMS\Util\Mac;
use SnmpQuery;

class Screenos extends \LibreNMS\OS implements OSPolling, ArpTableDiscovery
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $sess_data = snmp_get_multi_oid($this->getDeviceArray(), [
            '.1.3.6.1.4.1.3224.16.3.2.0',
            '.1.3.6.1.4.1.3224.16.3.3.0',
            '.1.3.6.1.4.1.3224.16.3.4.0',
        ]);

        if (! empty($sess_data)) {
            [$sessalloc, $sessmax, $sessfailed] = array_values($sess_data);

            $rrd_def = RrdDefinition::make()
                ->addDataset('allocate', 'GAUGE', 0, 3000000)
                ->addDataset('max', 'GAUGE', 0, 3000000)
                ->addDataset('failed', 'GAUGE', 0, 1000);

            $fields = [
                'allocate' => $sessalloc,
                'max' => $sessmax,
                'failed' => $sessfailed,
            ];

            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'screenos_sessions', $tags, $fields);

            $this->enableGraph('screenos_sessions');
        }
    }

    public function discoverArpTable(): Collection
    {
        $nsIpArpTable = SnmpQuery::walk('NETSCREEN-IP-ARP-MIB::nsIpArpTable')->table(1);

        if (! empty($nsIpArpTable)) {
            $nsIfInfo = array_flip(SnmpQuery::walk('NETSCREEN-INTERFACE-MIB::nsIfInfo')->pluck());
        }

        $arp = new Collection;

        foreach ($nsIpArpTable as $data) {
            $ifIndex = $nsIfInfo[$data['NETSCREEN-IP-ARP-MIB::nsIpArpIfIdx']];

            $arp->push(new Ipv4Mac([
                'port_id' => (int) PortCache::getIdFromIfIndex($ifIndex, $this->getDevice()),
                'mac_address' => Mac::parse($data['NETSCREEN-IP-ARP-MIB::nsIpArpMac'])->hex(),
                'ipv4_address' => $data['NETSCREEN-IP-ARP-MIB::nsIpArpIp'],
            ]));
        }

        return $arp;
    }
}
