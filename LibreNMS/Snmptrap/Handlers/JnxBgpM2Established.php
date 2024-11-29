<?php
/**
 * JnxBgpM2Established.php
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
 * @copyright  2018 KanREN, Inc.
 * @author     Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Enum\Severity;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use LibreNMS\Util\IP;
use Log;

class JnxBgpM2Established implements SnmptrapHandler
{
    /**
     * Handle snmptrap.
     * Data is pre-parsed and delivered as a Trap.
     *
     * @param  Device  $device
     * @param  Trap  $trap
     * @return void
     */
    public function handle(Device $device, Trap $trap)
    {
        $peerState = $trap->getOidData($trap->findOid('BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerState'));
        $peerAddr = IP::fromHexString($trap->getOidData($trap->findOid('BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAddr.')));

        $bgpPeer = $device->bgppeers()->where('bgpPeerIdentifier', $peerAddr)->first();

        if (! $bgpPeer) {
            Log::error('Unknown bgp peer handling bgpEstablished trap: ' . $peerAddr);

            return;
        }

        $bgpPeer->bgpPeerState = $peerState;

        if ($bgpPeer->isDirty('bgpPeerState')) {
            $trap->log("BGP Peer $peerAddr is now in the $peerState state", Severity::Ok);
        }

        $bgpPeer->save();
    }
}
