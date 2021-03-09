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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * Adva Threshold Exceeded Alarms.
 *
 * @link       https://www.librenms.org
 * @copyright  2018 KanREN, Inc
 * @author     Heath Barnhart <hbarnhart@kanren.net> & Neil Kahle <nkahle@kanren.net>
 */

namespace LibreNMS\Snmptrap\Handlers;

use App\Models\Device;
use Illuminate\Support\Str;
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
        $interval = $trap->getOidData($trap->findOid('CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdInterval'));
        $ifName = $trap->getOidData($trap->findOid('IF-MIB::ifName'));

        $thresholdMessage = $this->getThresholdMessage(
            $trap->getOidData($trap->findOid('CM-PERFORMANCE-MIB::cmEthernetAccPortThresholdVariable'))
        );

        Log::event("$ifName $thresholdMessage threshold exceeded for $interval", $device->device_id, 'trap', 2);
    }

    public function getThresholdMessage($thresholdOid)
    {
        foreach ($this->getThresholds() as $oid => $descr) {
            if (Str::contains($thresholdOid, $oid)) {
                return $descr;
            }
        }

        return 'unknown';
    }

    public function getThresholds()
    {
        return [
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsUAS' => 'unavailable seconds',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBF' => 'broadcast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBP' => 'broadcast frames received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESBS' => 'bytes sent',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESCAE' => 'crc align errors',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESC' => 'collisions',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESDE' => 'drop events',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESFS' => 'frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESF' => 'fragments',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESJ' => 'jabbers',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESMF' => 'multicast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESMP' => 'multicast pakcets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESOF' => 'oversize frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESOP' => 'oversize packets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESO' => 'octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP64' => '64 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP65' => '65 to 127 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP128' => '128 to 255 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP256' => '256 to 511 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP512' => '512 to 1023 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP1024' => '1024 to 1518 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP1519' => '1519 to MTU byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESP' => 'packets received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESUF' => 'unicast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsESUP' => 'unicast frames received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2CPFD' => 'layer 2 control protocol frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2CPFP' => 'layer 2 control protocol frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsABRRx' => 'average bit rate received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsABRTx' => 'average bit rate transmitted',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2PTRxFramesEncap' => 'layer 2 control protocol frames encapsulated',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsL2PTTxFramesDecap' => 'layer 2 control protocol frames decapsulated',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMaxRx' => 'instantaneous bit rate received max',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMaxTx' => 'instantaneous bit rate transmitted max',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMinRx' => 'instantaneous bit rate received min',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRMinTx' => 'instantaneous bit rate transmitted min',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRRx' => 'instantaneous bit rate received',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsIBRTx' => 'instantaneous bit rate transmitted',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsAclDropNoMatch' => 'acl drop no match',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsAclFwd2Cpu' => 'acl forwarded to cpu',
            'CM-PERFORMANCE-MIB::cmEthernetAccPortStatsDhcpDropNoAssocIf' => 'dhcp dropped due to no associated interface',
            'cmQosFlowPolicerStatsFMG' => 'frames marked green and passed',
            'cmQosFlowPolicerStatsFMY' => 'frames marked yellow and passed',
            'cmQosFlowPolicerStatsFMYD' => 'frames marked yellow and discarded',
            'cmQosFlowPolicerStatsFMRD' => 'frames marked red and discarded',
            'cmQosFlowPolicerStatsBytesIn' => 'total bytes in',
            'cmQosFlowPolicerStatsBytesOut' => 'total bytes out',
            'cmQosFlowPolicerStatsABR' => 'average bit rate',
            'cmAccPortQosShaperStatsBT' => 'bytes dequeued',
            'cmAccPortQosShaperStatsBTD' => 'bytes tail dropped',
            'cmAccPortQosShaperStatsFD' => 'frames dequeued',
            'cmAccPortQosShaperStatsFTD' => 'frames tail dropped',
            'cmAccPortQosShaperStatsBR' => 'bytes replicated',
            'cmAccPortQosShaperStatsFR' => 'frames replicated',
            'cmAccPortQosShaperStatsABRRL' => 'average bit rate - rate limited',
            'cmAccPortQosShaperStatsBREDD' => 'bytes random early discard, dropped',
            'cmAccPortQosShaperStatsFREDD' => 'frames random early discard, dropped',
        ];
    }
}
