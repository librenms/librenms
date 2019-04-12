<?php
/**
 * AdvaAccThresholdCrossingAlert.php
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

class AdvaAccThresholdCrossingAlert implements SnmptrapHandler
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
        $interval = $trap->getOidData($trap->findOid("CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval"));
        $ifName = $trap->getOidData($trap->findOid("IF-MIB::ifName"));
        $thresholdMessage = $this->handleThreshold($trap);
        Log::event("$ifName $thresholdMessage threshold exceeded for $interval", $device->device_id, 'trap', 2);
    }
    public static function handleThreshold($trap)
    {
        $threshOid = $trap->getOidData($trap->findOid("CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable"));
        switch (true) {
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsUAS') === 0:
                return 'unavailable seconds';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBF') === 0:
                return 'broadcast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBP') === 0:
                return 'broadcast frames received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBS') === 0:
                return 'bytes sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESCAE') === 0:
                return 'crc align errors';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESC') === 0:
                return 'collisions';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESDE') === 0:
                return 'drop events';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESFS') === 0:
                return 'frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESF ') === 0:
                return 'fragments';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESJ') === 0:
                return 'jabbers';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESMF') === 0:
                return 'multicast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESMP') === 0:
                return 'multicast pakcets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESOF') === 0:
                return 'oversize frames discarded';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESOP') === 0:
                return 'oversize packets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESO') === 0:
                return 'octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP64') === 0:
                return '64 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP65') === 0:
                return '65 to 127 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP128') === 0:
                return '128 to 255 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP256') === 0:
                return '256 to 511 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP512') === 0:
                return '512 to 1023 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP1024') === 0:
                return '1024 to 1518 byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP1519') === 0:
                return '1519 to MTU byte octets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP') === 0:
                return 'packets received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESUF') === 0:
                return 'unicast frames sent';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESUP') === 0:
                return 'unicast frames received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2CPFD') === 0:
                return 'layer 2 control protocol frames discarded';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2CPFP') === 0:
                return 'layer 2 control protocol frames processed';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsABRRx') === 0:
                return 'average bit rate received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsABRTx') === 0:
                return 'average bit rate transmitted';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2PTRxFramesEncap') === 0:
                return 'layer 2 control protocol frames encapsulated';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2PTTxFramesDecap') === 0:
                return 'layer 2 control protocol frames decapsulated';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMaxRx') === 0:
                return 'instantaneous bit rate received max';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMaxTx') === 0:
                return 'instantaneous bit rate transmitted max';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMinRx') === 0:
                return 'instantaneous bit rate received min';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMinTx') === 0:
                return 'instantaneous bit rate transmitted min';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRRx') === 0:
                return 'instantaneous bit rate received';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRTx') === 0:
                return 'instantaneous bit rate transmitted';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsAclDropNoMatch') === 0:
                return 'acl drop no match';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsAclFwd2Cpu') === 0:
                return 'acl forwarded to cpu';
            case strpos($threshOid, 'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsDhcpDropNoAssocIf') === 0:
                return 'dhcp dropped due to no associated interface';
            default:
                return 'unknown';
        }
    }
}
