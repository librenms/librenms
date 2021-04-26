<?php
/**
 * BgpEstablished.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class BgpEstablished implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param Device $device
     * @param Trap $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $state_oid = $trap->findOid('BGP4-MIB::bgpPeerState');
        $bgpPeerIp = substr($state_oid, 23);

        $bgpPeer = $device->bgppeers()->where('bgpPeerIdentifier', $bgpPeerIp)->first();

        if (! $bgpPeer) {
            Log::error('Unknown bgp peer handling bgpEstablished trap: ' . $bgpPeerIp);

            return;
        }

        $bgpPeer->bgpPeerState = $trap->getOidData($state_oid);

        if ($bgpPeer->isDirty('bgpPeerState')) {
            Log::event('SNMP Trap: BGP Up ' . $bgpPeer->bgpPeerIdentifier . ' ' . get_astext($bgpPeer->bgpPeerRemoteAs) . ' is now ' . $bgpPeer->bgpPeerState, $device->device_id, 'bgpPeer', 1, $bgpPeerIp);
        }

        $bgpPeer->save();
    }
}
