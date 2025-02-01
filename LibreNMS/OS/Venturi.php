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
            $data[0]['vServerMaxClients'],
            $data[0]['vServerMaxClientless'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('MaxClient', 'GAUGE', 0)
                ->addDataset('MaxClientless', 'GAUGE', 0);
            $fields = [
                'MaxClient' => $data[0]['vServerMaxClients'],
                'MaxClientless' => $data[0]['vServerMaxClientless'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_capacity_clients', $tags, $fields);
            $this->enableGraph('venturi_capacity_clients');
        }

        // venturi_capacity_bandwidth
        if (isset($data[0]['vServerMaxTcpBandwidth'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('MaxTcpBandwidth', 'GAUGE', 0);
            $fields = [
                'MaxTcpBandwidth' => $data[0]['vServerMaxTcpBandwidth'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_capacity_bandwidth', $tags, $fields);
            $this->enableGraph('venturi_capacity_bandwidth');
        }

        // venturi_subscriber_counts
        if (isset(
            $data['client.0']['vServerSubscriberTotalCount'],
            $data['clientless.0']['vServerSubscriberTotalCount'],
            $data['client.0']['vServerSubscriberCurrCount'],
            $data['clientless.0']['vServerSubscriberCurrCount'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TotalClientCount', 'COUNTER', 0)
                ->addDataset('TotalClientlessCount', 'COUNTER', 0)
                ->addDataset('CurrentClientCount', 'GAUGE', 0)
                ->addDataset('CurrentClientlessCount', 'GAUGE', 0);
            $fields = [
                'TotalClientCount' => $data['client.0']['vServerSubscriberTotalCount'],
                'TotalClientlessCount' => $data['clientless.0']['vServerSubscriberTotalCount'],
                'CurrentClientCount' => $data['client.0']['vServerSubscriberCurrCount'],
                'CurrentClientlessCount' => $data['clientless.0']['vServerSubscriberCurrCount'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_counts', $tags, $fields);
            $this->enableGraph('venturi_subscriber_counts');
        }

        // venturi_subscriber_failures
        if (isset(
            $data['client.0']['vServerSubscriberAuthenticationFailures'],
            $data['clientless.0']['vServerSubscriberAuthenticationFailures'],
            $data['client.0']['vServerSubscriberAbortedConnections'],
            $data['clientless.0']['vServerSubscriberAbortedConnections'],
            $data['client.0']['vServerSubscriberReassignmentFailures'],
            $data['clientless.0']['vServerSubscriberReassignmentFailures'],
            $data['client.0']['vServerSubscriberStandbyCount'],
            $data['clientless.0']['vServerSubscriberStandbyCount'],
            $data['client.0']['vServerSubscriberInactiveCount'],
            $data['clientless.0']['vServerSubscriberInactiveCount'],
            $data['client.0']['vServerSubscriberReassignmentCount'],
            $data['clientless.0']['vServerSubscriberReassignmentCount'],
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
                'ClientAuthenticationFailures' => $data['client.0']['vServerSubscriberAuthenticationFailures'],
                'ClientlessAuthenticationFailures' => $data['clientless.0']['vServerSubscriberAuthenticationFailures'],
                'ClientAbortedConnections' => $data['client.0']['vServerSubscriberAbortedConnections'],
                'ClientlessAbortedConnections' => $data['clientless.0']['vServerSubscriberAbortedConnections'],
                'ClientReassignmentFailures' => $data['client.0']['vServerSubscriberReassignmentFailures'],
                'ClientlessReassignmentFailures' => $data['clientless.0']['vServerSubscriberReassignmentFailures'],
                'ClientStandbyCount' => $data['client.0']['vServerSubscriberStandbyCount'],
                'ClientlessStandbyCount' => $data['clientless.0']['vServerSubscriberStandbyCount'],
                'ClientInactiveCount' => $data['client.0']['vServerSubscriberInactiveCount'],
                'ClientlessInactiveCount' => $data['clientless.0']['vServerSubscriberInactiveCount'],
                'ClientReassignmentCount' => $data['client.0']['vServerSubscriberReassignmentCount'],
                'ClientlessReassignmentCount' => $data['clientless.0']['vServerSubscriberReassignmentCount'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_failures', $tags, $fields);
            $this->enableGraph('venturi_subscriber_failures');
        }

        // venturi_subscriber_traffic
        if (isset(
            $data['client.0']['vServerSubscriberByteCount'],
            $data['clientless.0']['vServerSubscriberByteCount'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('ClientTraffic', 'COUNTER', 0)
                ->addDataset('ClientlessTraffic', 'COUNTER', 0);
            $fields = [
                'ClientTraffic' => $data['client.0']['vServerSubscriberByteCount'],
                'ClientlessTraffic' => $data['clientless.0']['vServerSubscriberByteCount'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_subscriber_traffic', $tags, $fields);
            $this->enableGraph('venturi_subscriber_traffic');
        }

        // venturi_transport_traffic
        if (isset(
            $data[0]['vServerTransportBytesSent'],
            $data[0]['vServerTransportBytesRecd'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TransportTrafficTx', 'COUNTER', 0)
                ->addDataset('TransportTrafficRx', 'COUNTER', 0);
            $fields = [
                'TransportTrafficTx' => $data[0]['vServerTransportBytesSent'],
                'TransportTrafficRx' => $data[0]['vServerTransportBytesRecd'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_traffic', $tags, $fields);
            $this->enableGraph('venturi_transport_traffic');
        }

        // venturi_transport_traffic_rexmit
        if (isset($data[0]['vServerTransportBytesRetransmitted'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('Retransmitted', 'COUNTER', 0);
            $fields = [
                'Retransmitted' => $data[0]['vServerTransportBytesRetransmitted'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_traffic_rexmit', $tags, $fields);
            $this->enableGraph('venturi_transport_traffic_rexmit');
        }

        // venturi_transport_compressor_traffic
        if (isset(
            $data[0]['vServerTransportBytesToComp'],
            $data[0]['vServerTransportBytesFromComp'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TrafficToCompressor', 'COUNTER', 0)
                ->addDataset('TrafficFromCompressor', 'COUNTER', 0);
            $fields = [
                'TrafficToCompressor' => $data[0]['vServerTransportBytesToComp'],
                'TrafficFromCompressor' => $data[0]['vServerTransportBytesFromComp'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_compressor_traffic', $tags, $fields);
            $this->enableGraph('venturi_transport_compressor_traffic');
        }

        // venturi_transport_packets
        if (isset(
            $data[0]['vServerTransportPacketsSent'],
            $data[0]['vServerTransportPacketsRecd'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TransportPacketsTx', 'COUNTER', 0)
                ->addDataset('TransportPacketsRx', 'COUNTER', 0);
            $fields = [
                'TransportPacketsTx' => $data[0]['vServerTransportPacketsSent'],
                'TransportPacketsRx' => $data[0]['vServerTransportPacketsRecd'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_packets', $tags, $fields);
            $this->enableGraph('venturi_transport_packets');
        }

        // venturi_transport_packets_rexit
        if (isset($data[0]['vServerTransportPacketsRetransmitted'])) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('Retransmitted', 'COUNTER', 0);
            $fields = [
                'Retransmitted' => $data[0]['vServerTransportPacketsRetransmitted'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_packets_rexit', $tags, $fields);
            $this->enableGraph('venturi_transport_packets_rexit');
        }

        // venturi_transport_undeliverables
        if (isset(
            $data[0]['vServerTransportUndeliverableToClients'],
            $data[0]['vServerTransportUndeliverableToComp'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('UndeliverableToClients', 'COUNTER', 0)
                ->addDataset('UndeliverableToComp', 'COUNTER', 0);
            $fields = [
                'UndeliverableToClients' => $data[0]['vServerTransportUndeliverableToClients'],
                'UndeliverableToComp' => $data[0]['vServerTransportUndeliverableToComp'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_undeliverables', $tags, $fields);
            $this->enableGraph('venturi_transport_undeliverables');
        }

        // venturi_transport_connections
        if (isset(
            $data[0]['vServerTransportTotalConnections'],
            $data[0]['vServerTransportCurrentConnections'],
        )) {
            $rrd_def = RrdDefinition::make()
                ->addDataset('TotalConnections', 'COUNTER', 0)
                ->addDataset('CurrentConnections', 'COUNTER', 0);
            $fields = [
                'TotalConnections' => $data[0]['vServerTransportTotalConnections'],
                'CurrentConnections' => $data[0]['vServerTransportCurrentConnections'],
            ];
            $tags = compact('rrd_def');
            $datastore->put($this->getDeviceArray(), 'venturi_transport_connections', $tags, $fields);
            $this->enableGraph('venturi_transport_connections');
        }
    }
}
