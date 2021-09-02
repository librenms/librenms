<?php
/*
 * Nios.php
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

use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\RRD\RrdDefinition;

class Nios extends \LibreNMS\OS implements OSPolling
{
    public function pollOS()
    {
        //#############
        // Create ddns update rrd
        //#############
        $mibs = 'IB-DNSONE-MIB';
        $oids = [
            'ibDDNSUpdateSuccess.0',
            'ibDDNSUpdateFailure.0',
            'ibDDNSUpdatePrerequisiteReject.0',
            'ibDDNSUpdateReject.0',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', $mibs);

        $rrd_def = RrdDefinition::make()
            ->addDataset('success', 'DERIVE', 0)
            ->addDataset('failure', 'DERIVE', 0)
            ->addDataset('reject', 'DERIVE', 0)
            ->addDataset('prereq_reject', 'DERIVE', 0);

        $fields = [
            'success' => $data[0]['ibDDNSUpdateSuccess'],
            'failure' => $data[0]['ibDDNSUpdateFailure'],
            'reject' => $data[0]['ibDDNSUpdateReject'],
            'prereq_reject' => $data[0]['ibDDNSUpdatePrerequisiteReject'],
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'ib_dns_dyn_updates', $tags, $fields);
        $this->enableGraph('ib_dns_dyn_updates');

        //#################
        // Create dns performance graph (latency)
        //#################
        $mibs = 'IB-PLATFORMONE-MIB';
        $oids = [
            'ibNetworkMonitorDNSNonAAT1AvgLatency.0',
            'ibNetworkMonitorDNSAAT1AvgLatency.0',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', $mibs);

        $rrd_def = RrdDefinition::make()
            ->addDataset('PerfAA', 'GAUGE', 0)
            ->addDataset('PerfnonAA', 'GAUGE', 0);

        $fields = [
            'PerfAA' => $data[0]['ibNetworkMonitorDNSAAT1AvgLatency'],
            'PerfnonAA' => $data[0]['ibNetworkMonitorDNSNonAAT1AvgLatency'],
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'ib_dns_performance', $tags, $fields);
        $this->enableGraph('ib_dns_performance');

        //#################
        // Create dns request return code graph
        //#################
        $mibs = 'IB-DNSONE-MIB';
        $oids = [
            'ibBindZoneFailure."summary"',
            'ibBindZoneNxDomain."summary"',
            'ibBindZoneNxRRset."summary"',
            'ibBindZoneSuccess."summary"',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', $mibs);

        $rrd_def = RrdDefinition::make()
            ->addDataset('success', 'DERIVE', 0)
            ->addDataset('failure', 'DERIVE', 0)
            ->addDataset('nxdomain', 'DERIVE', 0)
            ->addDataset('nxrrset', 'DERIVE', 0);

        $fields = [
            'success' => $data['"summary"']['ibBindZoneSuccess'],
            'failure' => $data['"summary"']['ibBindZoneFailure'],
            'nxdomain' => $data['"summary"']['ibBindZoneNxDomain'],
            'nxrrset' => $data['"summary"']['ibBindZoneNxRRset'],
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'ib_dns_request_return_codes', $tags, $fields);
        $this->enableGraph('ib_dns_request_return_codes');

        //#################
        // Create dhcp messages graph
        //#################
        $mibs = 'IB-DHCPONE-MIB';
        $oids = [
            'ibDhcpTotalNoOfAcks.0',
            'ibDhcpTotalNoOfDeclines.0',
            'ibDhcpTotalNoOfDiscovers.0',
            'ibDhcpTotalNoOfInforms.0',
            'ibDhcpTotalNoOfNacks.0',
            'ibDhcpTotalNoOfOffers.0',
            'ibDhcpTotalNoOfOthers.0',
            'ibDhcpTotalNoOfReleases.0',
            'ibDhcpTotalNoOfRequests.0',
        ];

        $data = snmp_get_multi($this->getDeviceArray(), $oids, '-OQUs', $mibs);

        $rrd_def = RrdDefinition::make()
            ->addDataset('ack', 'DERIVE', 0)
            ->addDataset('decline', 'DERIVE', 0)
            ->addDataset('discover', 'DERIVE', 0)
            ->addDataset('inform', 'DERIVE', 0)
            ->addDataset('nack', 'DERIVE', 0)
            ->addDataset('offer', 'DERIVE', 0)
            ->addDataset('other', 'DERIVE', 0)
            ->addDataset('release', 'DERIVE', 0)
            ->addDataset('request', 'DERIVE', 0);

        $fields = [
            'ack' => $data[0]['ibDhcpTotalNoOfAcks'],
            'decline' => $data[0]['ibDhcpTotalNoOfDeclines'],
            'discover' => $data[0]['ibDhcpTotalNoOfDiscovers'],
            'inform' => $data[0]['ibDhcpTotalNoOfInforms'],
            'nack' => $data[0]['ibDhcpTotalNoOfNacks'],
            'offer' => $data[0]['ibDhcpTotalNoOfOffers'],
            'other' => $data[0]['ibDhcpTotalNoOfOthers'],
            'release' => $data[0]['ibDhcpTotalNoOfReleases'],
            'request' => $data[0]['ibDhcpTotalNoOfRequests'],
        ];

        $tags = compact('rrd_def');
        data_update($this->getDeviceArray(), 'ib_dhcp_messages', $tags, $fields);
        $this->enableGraph('ib_dhcp_messages');
    }
}
