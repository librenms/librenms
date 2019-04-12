<?php
/**
 * AdvaNetThresholdCrossingAlert.php
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
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * Adva Threshold Exceeded Alarms.
 *
 * @package    LibreNMS
 * @link       http://librenms.org
 * @copyright  2018 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net> & Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use LibreNMS\Interfaces\SnmptrapHandler;
use LibreNMS\Snmptrap\Trap;
use Log;

class AdvaNetThresholdCrossingAlert implements SnmptrapHandler
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
        $interval = $trap->getOidData($trap->findOid("CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdInterval"));
        $ifName = $trap->getOidData($trap->findOid("IF-MIB::ifName"));
        $threshMessage = $this->handleThreshold($trap);
        Log::event("$ifName $threshMessage threshold exceeded for $interval", $device->device_id, 'trap', 2);
    }
    public static function handleThreshold($trap)
    {
        $threshOid = $trap->getOidData($trap->findOid("CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdVariable"));
        switch (true) {
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS') === 0:
                return 'unavailable seconds';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBF') === 0:
                return 'broadcast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBP') === 0:
                return 'broadcast frames received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBS') === 0:
                return 'bytes sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESCAE') === 0:
                return 'crc align errors';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESC') === 0:
                return 'collisions';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESDE') === 0:
                return 'drop events';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESFS') === 0:
                return 'frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESF ') === 0:
                return 'fragments';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESJ') === 0:
                return 'jabbers';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESMF') === 0:
                return 'multicast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESMP') === 0:
                return 'multicast pakcets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESOF') === 0:
                return 'oversize frames discarded';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESOP') === 0:
                return 'oversize packets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESO') === 0:
                return 'octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP64') === 0:
                return '64 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP65') === 0:
                return '65 to 127 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP128') === 0:
                return '128 to 255 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP256') === 0:
                return '256 to 511 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP512') === 0:
                return '512 to 1023 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP1024') === 0:
                return '1024 to 1518 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP1519') === 0:
                return '1519 to MTU byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP') === 0:
                return 'packets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESUF') === 0:
                return 'unicast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESUP') === 0:
                return 'unicast frames received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2CPFD') === 0:
                return 'layer 2 control protocol frames discarded';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2CPFP') === 0:
                return 'layer 2 control protocol frames processed';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsABRRx') === 0:
                return 'average bit rate received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsABRTx') === 0:
                return 'average bit rate transmitted';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2PTRxFramesEncap') === 0:
                return 'layer 2 control protocol frames encapsulated';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2PTTxFramesDecap') === 0:
                return 'layer 2 control protocol frames decapsulated';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMaxRx') === 0:
                return 'instantaneous bit rate received max';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMaxTx') === 0:
                return 'instantaneous bit rate transmitted max';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMinRx') === 0:
                return 'instantaneous bit rate received min';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMinTx') === 0:
                return 'instantaneous bit rate transmitted min';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRRx') === 0:
                return 'instantaneous bit rate received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRTx') === 0:
                return 'instantaneous bit rate transmitted';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsAclDropNoMatch') === 0:
                return 'acl drop no match';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsAclFwd2Cpu') === 0:
                return 'acl forwarded to cpu';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsDhcpDropNoAssocIf') === 0:
                return 'dhcp dropped due to no associated interface';
            default:
                return 'unknown';
        }
    }
}
