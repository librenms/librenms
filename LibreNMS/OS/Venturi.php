<?php

namespace LibreNMS\OS;

use LibreNMS\Interfaces\Data\DataStorageInterface;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Venturi extends OS implements OSPolling
{
    public function pollOS(DataStorageInterface $datastore): void
    {
        $data = snmp_get_multi($this->getDeviceArray(), [
            'VENTURI-SERVER-SYSTEM-MIB::vServerMaxClients.0',
            'VENTURI-SERVER-SYSTEM-MIB::vServerMaxClientless.0',
            'VENTURI-SERVER-SYSTEM-MIB::vServerMaxTcpBandwidth.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.1.0',
            'VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.2.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportPacketsSent.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRecd.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRetransmitted.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportBytesSent.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportBytesRecd.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportBytesRetransmitted.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToClients.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToComp.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportTotalConnections.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportCurrentConnections.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportBytesToComp.0',
            'VENTURI-SERVER-STATS-MIB::vServerTransportBytesFromComp.0',
        ], '-OQUs');

        // venturi_capacity_clients
        if (isset(
            $data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxClients.0'],
            $data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxClientless.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('MaxClient', 'GAUGE', 0)
                ->addDataset('MaxClientless', 'GAUGE', 0);
            $fields = [
                'MaxClient' => $data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxClients.0'],
                'MaxClientless' => $data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxClientless.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_capacity_clients', $tags, $fields);
            $this->enableGraph('venturi_capacity_clients');
        }

        // venturi_capacity_bandwidth
        if (isset($data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxTcpBandwidth.0'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('MaxTcpBandwidth', 'GAUGE', 0);
            $fields = [
                'MaxTcpBandwidth' => $data['VENTURI-SERVER-SYSTEM-MIB::vServerMaxTcpBandwidth.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_capacity_bandwidth', $tags, $fields);
            $this->enableGraph('venturi_capacity_bandwidth');
        }

        // venturi_subscriber_counts
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.2.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TotalClientCount', 'GAUGE', 0)
                ->addDataset('TotalClientlessCount', 'GAUGE', 0)
                ->addDataset('CurrentClientCount', 'GAUGE', 0)
                ->addDataset('CurrentClientlessCount', 'GAUGE', 0);
            $fields = [
                'TotalClientCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.1.0'],
                'TotalClientlessCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberTotalCount.2.0'],
                'CurrentClientCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.1.0'],
                'CurrentClientlessCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberCurrCount.2.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_counts', $tags, $fields);
            $this->enableGraph('venturi_subscriber_counts');
        }

        // venturi_subscriber_failures
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.2.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.1.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.2.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('ClientAuthenticationFailures', 'COUNTER', 0)
                ->addDataset('ClientlessAuthenticationFailures', 'COUNTER', 0)
                ->addDataset('ClientAbortedConnections', 'COUNTER', 0)
                ->addDataset('ClientlessAbortedConnections', 'COUNTER', 0)
                ->addDataset('ClientReassignmentFailures', 'COUNTER', 0)
                ->addDataset('ClientlessReassignmentFailures', 'COUNTER', 0)
                ->addDataset('ClientStandbyCount', 'COUNTER', 0)
                ->addDataset('ClientlessStandbyCount', 'COUNTER', 0)
                ->addDataset('ClientInactiveCount', 'COUNTER', 0)
                ->addDataset('ClientlessInactiveCount', 'COUNTER', 0)
                ->addDataset('ClientReassignmentCount', 'COUNTER', 0)
                ->addDataset('ClientlessReassignmentCount', 'COUNTER', 0);
            $fields = [
                'ClientAuthenticationFailures' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.1.0'],
                'ClientlessAuthenticationFailures' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAuthenticationFailures.2.0'],
                'ClientAbortedConnections' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.1.0'],
                'ClientlessAbortedConnections' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberAbortedConnections.2.0'],
                'ClientReassignmentFailures' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.1.0'],
                'ClientlessReassignmentFailures' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentFailures.2.0'],
                'ClientStandbyCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.1.0'],
                'ClientlessStandbyCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberStandbyCount.2.0'],
                'ClientInactiveCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.1.0'],
                'ClientlessInactiveCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberInactiveCount.2.0'],
                'ClientReassignmentCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.1.0'],
                'ClientlessReassignmentCount' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberReassignmentCount.2.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_failures', $tags, $fields);
            $this->enableGraph('venturi_subscriber_failures');
        }

        // venturi_subscriber_traffic
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('ClientTraffic', 'COUNTER', 0)
                ->addDataset('ClientlessTraffic', 'COUNTER', 0);
            $fields = [
                'ClientTraffic' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.0'],
                'ClientlessTraffic' => $data['VENTURI-SERVER-STATS-MIB::vServerSubscriberByteCount.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_traffic', $tags, $fields);
            $this->enableGraph('venturi_subscriber_traffic');
        }

        // venturi_transport_traffic
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesSent.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesRecd.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TransportTrafficTx', 'COUNTER', 0)
                ->addDataset('TransportTrafficRx', 'COUNTER', 0);
            $fields = [
                'TransportTrafficTx' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesSent.0'],
                'TransportTrafficRx' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesRecd.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_traffic', $tags, $fields);
            $this->enableGraph('venturi_transport_traffic');
        }

        // venturi_transport_traffic_rexmit
        if (isset($data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesRetransmitted.0'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('Retransmitted', 'COUNTER', 0);
            $fields = [
                'Retransmitted' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesRetransmitted.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_traffic_rexmit', $tags, $fields);
            $this->enableGraph('venturi_transport_traffic_rexmit');
        }

        // venturi_transport_compressor_traffic
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesToComp.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesFromComp.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TrafficToCompressor', 'COUNTER', 0)
                ->addDataset('TrafficFromCompressor', 'COUNTER', 0);
            $fields = [
                'TrafficToCompressor' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesToComp.0'],
                'TrafficFromCompressor' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportBytesFromComp.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_compressor_traffic', $tags, $fields);
            $this->enableGraph('venturi_transport_compressor_traffic');
        }

        // venturi_transport_packets
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsSent.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRecd.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TransportTrafficTx', 'COUNTER', 0)
                ->addDataset('TransportTrafficRx', 'COUNTER', 0);
            $fields = [
                'TransportTrafficTx' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsSent.0'],
                'TransportTrafficRx' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRecd.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_packets', $tags, $fields);
            $this->enableGraph('venturi_transport_packets');
        }

        // venturi_transport_packets_rexit
        if (isset($data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRetransmitted.0'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('Retransmitted', 'COUNTER', 0);
            $fields = [
                'Retransmitted' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportPacketsRetransmitted.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_packets_rexit', $tags, $fields);
            $this->enableGraph('venturi_transport_packets_rexit');
        }

        // venturi_transport_undeliverables
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToClients.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToComp.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('UndeliverableToClients', 'COUNTER', 0)
                ->addDataset('UndeliverableToComp', 'COUNTER', 0);
            $fields = [
                'UndeliverableToClients' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToClients.0'],
                'UndeliverableToComp' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportUndeliverableToComp.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_undeliverables', $tags, $fields);
            $this->enableGraph('venturi_transport_undeliverables');
        }

        // venturi_transport_connections
        if (isset(
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportTotalConnections.0'],
            $data['VENTURI-SERVER-STATS-MIB::vServerTransportCurrentConnections.0'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TotalConnections', 'COUNTER', 0)
                ->addDataset('CurrentConnections', 'COUNTER', 0);
            $fields = [
                'TotalConnections' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportTotalConnections.0'],
                'CurrentConnections' => $data['VENTURI-SERVER-STATS-MIB::vServerTransportCurrentConnections.0'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_connections', $tags, $fields);
            $this->enableGraph('venturi_transport_connections');
        }
    }
}
