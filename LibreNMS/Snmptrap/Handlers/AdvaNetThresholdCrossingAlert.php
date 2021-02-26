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
        $interval = $trap->getOidData($trap->findOid('CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdInterval'));
        $ifName = $trap->getOidData($trap->findOid('IF-MIB::ifName'));
        $threshMessage = $this->getThresholdMessage(
            $trap->getOidData($trap->findOid('CM-PERFORMANCE-MIB::cmEthernetNetPortThresholdVariable'))
        );

        Log::event("$ifName $threshMessage threshold exceeded for $interval", $device->device_id, 'trap', 2);
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
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsUAS' => 'unavailable seconds',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBF' => 'broadcast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBP' => 'broadcast frames received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESBS' => 'bytes sent',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESCAE' => 'crc align errors',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESC' => 'collisions',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESDE' => 'drop events',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESFS' => 'frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESF' => 'fragments',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESJ' => 'jabbers',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESMF' => 'multicast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESMP' => 'multicast pakcets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESOF' => 'oversize frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESOP' => 'oversize packets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESO' => 'octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP64' => '64 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP65' => '65 to 127 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP128' => '128 to 255 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP256' => '256 to 511 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP512' => '512 to 1023 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP1024' => '1024 to 1518 byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP1519' => '1519 to MTU byte octets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESP' => 'packets received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESUF' => 'unicast frames sent',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsESUP' => 'unicast frames received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2CPFD' => 'layer 2 control protocol frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2CPFP' => 'layer 2 control protocol frames discarded',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsABRRx' => 'average bit rate received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsABRTx' => 'average bit rate transmitted',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2PTRxFramesEncap' => 'layer 2 control protocol frames encapsulated',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsL2PTTxFramesDecap' => 'layer 2 control protocol frames decapsulated',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMaxRx' => 'instantaneous bit rate received max',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMaxTx' => 'instantaneous bit rate transmitted max',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMinRx' => 'instantaneous bit rate received min',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRMinTx' => 'instantaneous bit rate transmitted min',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRRx' => 'instantaneous bit rate received',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsIBRTx' => 'instantaneous bit rate transmitted',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsAclDropNoMatch' => 'acl drop no match',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsAclFwd2Cpu' => 'acl forwarded to cpu',
            'CM-PERFORMANCE-MIB::cmEthernetNetPortStatsDhcpDropNoAssocIf' => 'dhcp dropped due to no associated interface',
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
