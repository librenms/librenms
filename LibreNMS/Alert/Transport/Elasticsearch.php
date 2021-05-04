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

class Elasticsearch extends Transport
{
    public function deliverAlert($obj, $opts)
    {
        if (! empty($this->config)) {
            $opts['es_host'] = $this->config['es-host'];
            $opts['es_port'] = $this->config['es-port'];
            $opts['es_index'] = $this->config['es-pattern'];
            $opts['es_proxy'] = $this->config['es-proxy'];
        }

        return $this->contactElasticsearch($obj, $opts);
    }

    public function contactElasticsearch($obj, $opts)
    {
        $es_host = '127.0.0.1';
        $es_port = 9200;
        $index = strftime('librenms-%Y.%m.%d');
        $type = 'alert';
        $severity = $obj['severity'];
        $device = device_by_id_cache($obj['device_id']); // for event logging

        if (! empty($opts['es_host'])) {
            if (preg_match('/[a-zA-Z]/', $opts['es_host'])) {
                $es_host = gethostbyname($opts['es_host']);
                if ($es_host === $opts['es_host']) {
                    return 'Alphanumeric hostname found but does not resolve to an IP.';
                }
            } elseif (filter_var($opts['es_host'], FILTER_VALIDATE_IP)) {
                $es_host = $opts['es_host'];
            } else {
                return 'Elasticsearch host is not a valid IP: ' . $opts['es_host'];
            }
        }

        if (! empty($opts['es_port']) && preg_match("/^\d+$/", $opts['es_port'])) {
            $es_port = $opts['es_port'];
        }

        if (! empty($opts['es_index'])) {
            $index = strftime($opts['es_index']);
        }

        $host = $es_host . ':' . $es_port . '/' . $index . '/' . $type;

        switch ($obj['state']) {
            case AlertState::RECOVERED:
                $state = 'ok';
                break;
            case AlertState::ACTIVE:
                $state = $severity;
                break;
            case AlertState::ACKNOWLEDGED:
                $state = 'acknowledged';
                break;
            case AlertState::WORSE:
                $state = 'worse';
                break;
            case AlertState::BETTER:
                $state = 'better';
                break;
            default:
                $state = 'unknown';
                break;
        }

        $data = [
            '@timestamp' => date('c'),
            'host' => gethostname(),
            'location' => $obj['location'],
            'title' => $obj['name'],
            'message' => $obj['string'],
            'device_id' => $obj['device_id'],
            'device_name' => $obj['hostname'],
            'device_hardware' => $obj['hardware'],
            'device_version' => $obj['version'],
            'state' => $state,
            'severity' => $severity,
            'first_occurrence' => $obj['timestamp'],
            'entity_type' => 'device',
            'entity_tab' => 'overview',
            'entity_id' => $obj['device_id'],
            'entity_name' => $obj['hostname'],
            'entity_descr' => $obj['sysDescr'],
        ];

        if (! empty($obj['faults'])) {
            foreach ($obj['faults'] as $k => $v) {
                $curl = curl_init();
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
                        $data['entity_name'] = 'local: ' . $v['bgpPeerLocalAddr'] . ' - AS' . $obj['bgpLocalAs'];
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
                $alert_message = json_encode($data);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                if ($opts['es_proxy'] === true) {
                    set_curl_proxy($curl);
                }
                curl_setopt($curl, CURLOPT_URL, $host);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

                $ret = curl_exec($curl);
                $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                if ($code != 200 && $code != 201) {
                    return $host . ' returned HTTP Status code ' . $code . ' for ' . $alert_message;
                }
            }
        } else {
            $curl = curl_init();
            $alert_message = json_encode($data);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            if ($opts['es_proxy'] === true) {
                set_curl_proxy($curl);
            }
            curl_setopt($curl, CURLOPT_URL, $host);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $alert_message);

            $ret = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($code != 200 && $code != 201) {
                return $host . ' returned HTTP Status code ' . $code . ' for ' . $alert_message;
            }
        }

        return true;
    }

    public static function configTemplate()
    {
        return [
            'config' => [
                [
                    'title' => 'Host',
                    'name' => 'es-host',
                    'descr' => 'Elasticsearch Host',
                    'type' => 'text',
                ],
                [
                    'title' => 'Port',
                    'name' => 'es-port',
                    'descr' => 'Elasticsearch Port',
                    'type' => 'text',
                ],
                [
                    'title' => 'Index Pattern',
                    'name' => 'es-pattern',
                    'descr' => 'Elasticsearch Index Pattern',
                    'type' => 'text',
                ],
                [
                    'title' => 'Use proxy if configured?',
                    'name' => 'es-proxy',
                    'descr' => 'Elasticsearch Proxy',
                    'type' => 'checkbox',
                    'default' => false,
                ],
            ],
            'validation' => [
                'es-host' => 'required|string',
                'es-port' => 'required|string',
                'es-pattern' => 'required|string',
            ],
        ];
    }
}
