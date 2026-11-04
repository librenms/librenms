<?php

/**
 * NokiaIsam.php
 *
 * Nokia ISAM
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @link       https://www.librenms.org
 *
 * @copyright  2026 Bernhard Steinert
 * @author     Bernhard Steinert <bernhard@steinert.net>
 */

namespace LibreNMS\OS;

use App\Facades\PortCache;
use App\Models\Ipv4Address;
use Illuminate\Support\Collection;
use LibreNMS\Interfaces\Discovery\Ipv4AddressDiscovery;
use LibreNMS\OS;
use LibreNMS\Util\IPv4;
use SnmpQuery;

class NokiaIsam extends OS implements Ipv4AddressDiscovery
{
    // ISAM keeps its IP interfaces in the ihub context (like its NT ports), the default context only has 127.x
    public function discoverIpv4Addresses(): Collection
    {
        $device = $this->getDevice();
        $ips = new Collection;

        foreach (array_unique([...$device->getVrfContexts(), 'ihub']) as $context) {
            $ips = $ips->merge(SnmpQuery::context($context)->allowUnordered()->hideMib()->walk([
                'IP-MIB::ipAdEntIfIndex',
                'IP-MIB::ipAdEntNetMask',
            ])->mapTable(function ($data, $ipAddr) use ($context, $device) {
                if (! IPv4::isValid($ipAddr)) {
                    return null;
                }

                return new Ipv4Address([
                    'port_id' => PortCache::getIdFromIfIndex($data['ipAdEntIfIndex'] ?? 0, $device),
                    'ipv4_address' => $ipAddr,
                    'ipv4_prefixlen' => $data['ipAdEntNetMask'] ?? '',
                    'context_name' => $context == 'ihub' ? '' : $context,
                ]);
            }));
        }

        return $ips->filter();
    }
}
