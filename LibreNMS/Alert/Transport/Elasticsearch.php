<?php
/* LibreNMS
 *
 * Copyright (C) 2017 Paul Blasquez <pblasquez@gmail.com>
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
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

namespace LibreNMS\Alert\Transport;

use LibreNMS\Alert\Transport;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Elasticsearch extends Transport
{
    public function deliverAlert(array $alert_data): bool
    {
        $es_host = $this->config['es-host'];
        $es_port = $this->config['es-port'] ?: 9200;
        $index = date($this->config['es-pattern'] ?: "\l\i\b\\r\\e\\n\m\s\-Y.m.d");
        $type = 'alert';
        $severity = $alert_data['severity'];

        $host = $es_host . ':' . $es_port . '/' . $index . '/' . $type;

        $state = match ($alert_data['state']) {
            AlertState::RECOVERED => 'ok',
            AlertState::ACTIVE => $severity,
            AlertState::ACKNOWLEDGED => 'acknowledged',
            AlertState::WORSE => 'worse',
            AlertState::BETTER => 'better',
            default => 'unknown',
        };

        $data = [
            '@timestamp' => date('c'),
            'host' => gethostname(),
            'location' => $alert_data['location'],
            'title' => $alert_data['name'],
            'message' => $alert_data['string'],
            'device_id' => $alert_data['device_id'],
            'device_name' => $alert_data['hostname'],
            'device_hardware' => $alert_data['hardware'],
            'device_version' => $alert_data['version'],
            'state' => $state,
            'severity' => $severity,
            'first_occurrence' => $alert_data['timestamp'],
            'entity_type' => 'device',
            'entity_tab' => 'overview',
            'entity_id' => $alert_data['device_id'],
            'entity_name' => $alert_data['hostname'],
            'entity_descr' => $alert_data['sysDescr'],
        ];

        foreach ($alert_data['faults'] as $k => $v) {
            $data['message'] = $v['string'];
            switch (true) {
                case array_key_exists('port_id', $v):
                    $data['entity_type'] = 'port';
                    $data['entity_tab'] = 'port';
                    $data['entity_id'] = $v['port_id'];
                    $data['entity_name'] = $v['ifName'];
                    $data['entity_descr'] = $v['ifAlias'];
                    break;
                case array_key_exists('sensor_id', $v):
                    $data['entity_type'] = $v['sensor_class'];
                    $data['entity_tab'] = 'health';
                    $data['entity_id'] = $v['sensor_id'];
                    $data['entity_name'] = $v['sensor_descr'];
                    $data['entity_descr'] = $v['sensor_type'];
                    break;
                case array_key_exists('mempool_id', $v):
                    $data['entity_type'] = 'mempool';
                    $data['entity_tab'] = 'health';
                    $data['entity_id'] = $v['mempool_id'];
                    $data['entity_name'] = $v['mempool_index'];
                    $data['entity_descr'] = $v['mempool_descr'];
                    break;
                case array_key_exists('storage_id', $v):
                    $data['entity_type'] = 'storage';
                    $data['entity_tab'] = 'health';
                    $data['entity_id'] = $v['storage_id'];
                    $data['entity_name'] = $v['storage_index'];
                    $data['entity_descr'] = $v['storage_descr'];
                    break;
                case array_key_exists('processor_id', $v):
                    $data['entity_type'] = 'processor';
                    $data['entity_tab'] = 'health';
                    $data['entity_id'] = $v['processor_id'];
                    $data['entity_name'] = $v['processor_type'];
                    $data['entity_descr'] = $v['processor_descr'];
                    break;
                case array_key_exists('bgpPeer_id', $v):
                    $data['entity_type'] = 'bgp';
                    $data['entity_tab'] = 'routing';
                    $data['entity_id'] = $v['bgpPeer_id'];
                    $data['entity_name'] = 'local: ' . $v['bgpPeerLocalAddr'] . ' - AS' . $alert_data['bgpLocalAs'];
                    $data['entity_descr'] = 'remote: ' . $v['bgpPeerIdentifier'] . ' - AS' . $v['bgpPeerRemoteAs'];
                    break;
                case array_key_exists('tunnel_id', $v):
                    $data['entity_type'] = 'ipsec_tunnel';
                    $data['entity_tab'] = 'routing';
                    $data['entity_id'] = $v['tunnel_id'];
                    $data['entity_name'] = $v['tunnel_name'];
                    $data['entity_descr'] = 'local: ' . $v['local_addr'] . ':' . $v['local_port'] . ', remote: ' . $v['peer_addr'] . ':' . $v['peer_port'];
                    break;
                default:
                    $data['entity_type'] = 'generic';
                    break;
            }
        }

        $client = Http::client();

        // silly, just use no_proxy
        if ($this->config['es-proxy'] !== 'on') {
            $client->withOptions([
                'proxy' => [
                    'http' => '',
                    'https' => '',
                ],
            ]);
        }

        $res = $client->post($host, $data);

        if ($res->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $res->status(), $res->body(), $data['message'] ?? '', $data);
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Host',
                    'name' => 'es-host',
                    'descr' => 'Elasticsearch Host',
                    'type' => 'text',
                    'default' => '127.0.0.1',
                ],
                [
                    'title' => 'Port',
                    'name' => 'es-port',
                    'descr' => 'Elasticsearch Port',
                    'type' => 'text',
                    'default' => 9200,
                ],
                [
                    'title' => 'Index Pattern',
                    'name' => 'es-pattern',
                    'descr' => 'Elasticsearch Index Pattern | Default: \l\i\b\\r\\e\\n\m\s\-Y.m.d | Format: https://www.php.net/manual/en/function.date.php',
                    'type' => 'text',
                    'default' => "\l\i\b\\r\\e\\n\m\s\-Y.m.d",
                ],
                [
                    'title' => 'Use proxy if configured?',
                    'name' => 'es-proxy',
                    'descr' => 'Elasticsearch Proxy (Deprecated: just use no_proxy setting to exclude ES server)',
                    'type' => 'checkbox',
                    'default' => true,
                ],
            ],
            'validation' => [
                'es-host' => 'required|ip_or_hostname',
                'es-port' => 'integer|between:1,65535',
                'es-pattern' => 'string',
            ],
        ];
    }
}
