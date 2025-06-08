<?php

/**
 * Aos6.php
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
 * @link       https://www.librenms.org
 *
 * @author     Peca Nesovanovic <peca.nesovanovic@sattrakt.com>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\PortsFdb;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\FdbTableDiscovery;
use LibreNMS\OS;
use SnmpQuery;

class Aos6 extends OS implements FdbTableDiscovery
{
    public function discoverFdbTable(): Collection
    {
        $fdbt = new Collection;

        // try nokia/ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition
        $dot1d = SnmpQuery::mibDir('nokia')->hideMib()->walk('ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressDisposition')->table();

        if (! empty($dot1d)) {
            echo 'AOS6 MAC-ADDRESS-MIB: ';
            $fdbPort_table = [];
            foreach ($dot1d['slMacAddressDisposition'] as $portLocal => $data) {
                foreach ($data as $vlanLocal => $data2) {
                    if (! isset($fdbPort_table[$vlanLocal]['dot1qTpFdbPort'])) {
                        $fdbPort_table[$vlanLocal] = ['dot1qTpFdbPort' => []];
                    }
                    foreach ($data2 as $macLocal => $one) {
                        $fdbPort_table[$vlanLocal]['dot1qTpFdbPort'][$macLocal] = $portLocal;
                    }
                }
            }
        }

        if (! empty($fdbPort_table)) {
            $dot1dBasePortIfIndex = SnmpQuery::hideMib()->walk('BRIDGE-MIB::dot1dBasePortIfIndex')->table();
            $dot1dBasePortIfIndex = (! empty($dot1dBasePortIfIndex)) ? array_shift($dot1dBasePortIfIndex) : [];

            foreach ($fdbPort_table as $vlanIdx => $macData) {
                foreach ($macData as $mac_address => $portIdx) {
                    $ifIndex = $dot1dBasePortIfIndex[$portIdx] ?? 0;
                    $port_id = PortCache::getIdFromIfIndex($ifIndex, $this->getDeviceId()) ?? 0;
                    $fdbt->push(new PortsFdb([
                        'port_id' => $port_id,
                        'mac_address' => $mac_address,
                        'vlan_id' => $vlanIdx,
                    ]));
                }
            }
        }

        return $fdbt->filter();
    }
}
