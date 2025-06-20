<?php

/**
 * Aos7.php
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

class Aos7 extends OS implements FdbTableDiscovery
{
    public function discoverFdbTable(): Collection
    {
        $fdbt = new Collection;

        $dot1d = SnmpQuery::mibDir('nokia/aos7')->hideMib()->walk('ALCATEL-IND1-MAC-ADDRESS-MIB::slMacAddressGblManagement')->table();

        if (! empty($dot1d)) {
            echo 'AOS7+ MAC-ADDRESS-MIB:';
            $fdbPort_table = [];
            foreach ($dot1d['slMacAddressGblManagement'] as $slMacDomain => $data) {
                foreach ($data as $slLocaleType => $data2) {
                    foreach ($data2 as $portLocal => $data3) {
                        foreach ($data3 as $vlanLocal => $data4) {
                            if (! isset($fdbPort_table[$vlanLocal]['dot1qTpFdbPort'])) {
                                $fdbPort_table[$vlanLocal] = ['dot1qTpFdbPort' => []];
                            }
                            foreach ($data4[0] as $macLocal => $one) {
                                $fdbPort_table[$vlanLocal]['dot1qTpFdbPort'][$macLocal] = $portLocal;
                            }
                        }
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
