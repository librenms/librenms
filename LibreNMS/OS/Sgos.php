<?php
/**
 * Sgos.php
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
 * @copyright  2018 Tony Murray
 * @author     Tony Murray <murraytony@gmail.com>
 */

namespace LibreNMS\OS;

use LibreNMS\Device\Processor;
use LibreNMS\Interfaces\Discovery\ProcessorDiscovery;
use LibreNMS\Interfaces\Polling\OSPolling;
use LibreNMS\OS;
use LibreNMS\RRD\RrdDefinition;

class Sgos extends OS implements ProcessorDiscovery, OSPolling
{
    public function pollOS(): void
    {
        $oid_list = [
            'sgProxyHttpClientRequestRate.0',
            'sgProxyHttpClientConnections.0',
            'sgProxyHttpClientConnectionsActive.0',
            'sgProxyHttpClientConnectionsIdle.0',
            'sgProxyHttpServerConnections.0',
            'sgProxyHttpServerConnectionsActive.0',
            'sgProxyHttpServerConnectionsIdle.0',
        ];

        $sgos = snmp_get_multi($this->getDeviceArray(), $oid_list, '-OUQs', 'BLUECOAT-SG-PROXY-MIB');

        if (is_numeric($sgos[0]['sgProxyHttpClientRequestRate'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('requests', 'GAUGE', 0),
            ];
            $fields = [
                'requests' => $sgos[0]['sgProxyHttpClientRequestRate'],
            ];

            data_update($this->getDeviceArray(), 'sgos_average_requests', $tags, $fields);

            $this->enableGraph('sgos_average_requests');
            echo ' HTTP Req Rate';
        }

        if (is_numeric($sgos[0]['sgProxyHttpClientConnections'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('client_conn', 'GAUGE', 0),
            ];
            $fields = [
                'client_conn' => $sgos[0]['sgProxyHttpClientConnections'],
            ];

            data_update($this->getDeviceArray(), 'sgos_client_connections', $tags, $fields);

            $this->enableGraph('sgos_client_connections');
            echo ' Client Conn';
        }

        if (is_numeric($sgos[0]['sgProxyHttpServerConnections'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('server_conn', 'GAUGE', 0),
            ];
            $fields = [
                'server_conn' => $sgos[0]['sgProxyHttpServerConnections'],
            ];

            data_update($this->getDeviceArray(), 'sgos_server_connections', $tags, $fields);

            $this->enableGraph('sgos_server_connections');
            echo ' Server Conn';
        }

        if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsActive'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('client_conn_active', 'GAUGE', 0),
            ];
            $fields = [
                'client_conn_active' => $sgos[0]['sgProxyHttpClientConnectionsActive'],
            ];

            data_update($this->getDeviceArray(), 'sgos_client_connections_active', $tags, $fields);

            $this->enableGraph('sgos_client_connections_active');
            echo ' Client Conn Active';
        }

        if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsActive'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('server_conn_active', 'GAUGE', 0),
            ];
            $fields = [
                'server_conn_active' => $sgos[0]['sgProxyHttpServerConnectionsActive'],
            ];

            data_update($this->getDeviceArray(), 'sgos_server_connections_active', $tags, $fields);

            $this->enableGraph('sgos_server_connections_active');
            echo ' Server Conn Active';
        }

        if (is_numeric($sgos[0]['sgProxyHttpClientConnectionsIdle'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('client_idle', 'GAUGE', 0),
            ];
            $fields = [
                'client_idle' => $sgos[0]['sgProxyHttpClientConnectionsIdle'],
            ];

            data_update($this->getDeviceArray(), 'sgos_client_connections_idle', $tags, $fields);

            $this->enableGraph('sgos_client_connections_idle');
            echo ' Client Conne Idle';
        }

        if (is_numeric($sgos[0]['sgProxyHttpServerConnectionsIdle'] ?? null)) {
            $tags = [
                'rrd_def' => RrdDefinition::make()->addDataset('server_idle', 'GAUGE', 0),
            ];
            $fields = [
                'server_idle' => $sgos[0]['sgProxyHttpServerConnectionsIdle'],
            ];

            data_update($this->getDeviceArray(), 'sgos_server_connections_idle', $tags, $fields);

            $this->enableGraph('sgos_server_connections_idle');
            echo ' Server Conn Idle';
        }
    }

    /**
     * Discover processors.
     * Returns an array of LibreNMS\Device\Processor objects that have been discovered
     *
     * @return array Processors
     */
    public function discoverProcessors()
    {
        $data = snmpwalk_group($this->getDeviceArray(), 'sgProxyCpuCoreBusyPerCent', 'BLUECOAT-SG-PROXY-MIB');

        $processors = [];
        $count = 1;
        foreach ($data as $index => $entry) {
            $processors[] = Processor::discover(
                $this->getName(),
                $this->getDeviceId(),
                ".1.3.6.1.4.1.3417.2.11.2.4.1.8.$index",
                zeropad($index),
                "Processor $count",
                1,
                $entry['s5ChasUtilCPUUsageLast10Minutes']
            );

            $count++;
        }

        return $processors;
    }
}
