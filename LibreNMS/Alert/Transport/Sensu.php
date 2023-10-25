<?php
/* Copyright (C) 2020 Adam Bishop <adam@omega.org.uk>
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>. */

/**
 * API Transport
 *
 * @author Adam Bishop <adam@omega.org.uk>
 * @copyright 2020 Adam Bishop, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use Illuminate\Support\Facades\Log;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;
use LibreNMS\Exceptions\AlertTransportDeliveryException;
use LibreNMS\Util\Http;

class Sensu extends Transport
{
    // Sensu alert coding
    public const OK = 0;
    public const WARNING = 1;
    public const CRITICAL = 2;
    public const UNKNOWN = 3;

    private static array $status = [
        'ok' => self::OK,
        'warning' => self::WARNING,
        'critical' => self::CRITICAL,
    ];

    public function deliverAlert(array $alert_data): bool
    {
        $sensu_opts['source-key'] = $this->config['sensu-source-key'];

        $url = $this->config['sensu-url'] ?: 'http://127.0.0.1:3031';
        $client = Http::client();

        // The Sensu agent should be running on the poller - events can be sent directly to the backend but this has not been tested, and likely needs mTLS.
        // The agent API is documented at https://docs.sensu.io/sensu-go/latest/reference/agent/#create-monitoring-events-using-the-agent-api

        $health_check = $client->get($url . '/healthz')->status();
        if ($health_check !== 200) {
            throw new AlertTransportDeliveryException($alert_data, $health_check, 'Sensu API is not responding');
        }

        if ($alert_data['state'] !== AlertState::RECOVERED && $alert_data['state'] !== AlertState::ACKNOWLEDGED && $alert_data['alerted'] === 0) {
            // If this is the first event, send a forced "ok" dated (rrd.step / 2) seconds ago to tell Sensu the last time the check was healthy
            $data = $this->generateData($alert_data, self::OK, (int) round(Config::get('rrd.step', 300) / 2));
            Log::debug('Sensu transport sent last good event to socket: ', $data);

            $result = $client->post($url . '/events', $data);
            if ($result->status() !== 202) {
                throw new AlertTransportDeliveryException($alert_data, $result->status(), $result->body(), json_encode($data), $this->config);
            }

            sleep(5);
        }

        $data = $this->generateData($alert_data, $this->calculateStatus($alert_data['state'], $alert_data['severity']));

        $result = $client->post($url . '/events', $data);

        if ($result->successful()) {
            return true;
        }

        throw new AlertTransportDeliveryException($alert_data, $result->status(), $result->body(), json_encode($data), $sensu_opts);
    }

    private function generateData(array $alert_data, int $status, int $offset = 0): array
    {
        $namespace = $this->config['sensu-namespace'] ?: 'default';

        return [
            'check' => [
                'metadata' => [
                    'name' => $this->checkName($this->config['sensu-prefix'], $alert_data['name']),
                    'namespace' => $namespace,
                    'annotations' => $this->generateAnnotations($alert_data),
                ],
                'command' => sprintf('LibreNMS: %s', $alert_data['builder']),
                'executed' => time() - $offset,
                'interval' => Config::get('rrd.step', 300),
                'issued' => time() - $offset,
                'output' => $alert_data['msg'],
                'status' => $status,
            ],
            'entity' => [
                'metadata' => [
                    'name' => $this->getEntityName($alert_data),
                    'namespace' => $namespace,
                ],
                'system' => [
                    'hostname' => $alert_data['hostname'],
                    'os' => $alert_data['os'],
                ],
            ],
        ];
    }

    private function generateAnnotations(array $alert_data): array
    {
        return array_filter([
            'generated-by' => 'LibreNMS',
            'acknowledged' => $alert_data['state'] === AlertState::ACKNOWLEDGED ? 'true' : 'false',
            'contact' => $alert_data['sysContact'],
            'description' => $alert_data['sysDescr'],
            'location' => $alert_data['location'],
            'documentation' => $alert_data['proc'],
            'librenms-notes' => $alert_data['notes'],
            'librenms-device-id' => strval($alert_data['device_id']),
            'librenms-rule-id' => strval($alert_data['rule_id']),
            'librenms-status-reason' => $alert_data['status_reason'],
        ], function (?string $s): bool {
            return (bool) strlen($s); // strlen returns 0 for null, false or '', but 1 for integer 0 - unlike empty()
        });
    }

    private function calculateStatus(int $state, string $severity): int
    {
        // Sensu only has a single short (status) to indicate both severity and status, so we need to map LibreNMS' state and severity onto it

        if ($state === AlertState::RECOVERED) {
            // LibreNMS alert is resolved, send ok
            return self::OK;
        }

        return self::$status[$severity] ?? self::UNKNOWN;
    }

    private function getEntityName(array $obj): string
    {
        $key = $this->config['sensu-source-key'] ?: 'display';

        return $key === 'shortname' ? $this->shortenName($obj['display']) : $obj[$key];
    }

    private function shortenName(string $name): string
    {
        // Shrink the last domain components - e.g. librenms.corp.example.net becomes librenms.cen
        $components = explode('.', $name);
        $count = count($components);
        $trim = min([3, $count - 1]);
        $result = '';

        if ($count <= 2) {  // Can't be shortened
            return $name;
        }

        for ($i = $count - 1; $i >= $count - $trim; $i--) {
            // Walk the array in reverse order, taking the first letter from the $trim sections
            $result = sprintf('%s%s', substr($components[$i], 0, 1), $result);
            unset($components[$i]);
        }

        return sprintf('%s.%s', implode('.', $components), $result);
    }

    private function checkName(string $prefix, string $name): string
    {
        $check = strtolower(str_replace(' ', '-', $name));

        if ($prefix) {
            return sprintf('%s-%s', $prefix, $check);
        }

        return $check;
    }

    public static function configTemplate(): array
    {
        return [
            'config' => [
                [
                    'title' => 'Sensu Endpoint',
                    'name' => 'sensu-url',
                    'descr' => 'To configure the agent API, see https://docs.sensu.io/sensu-go/latest/reference/agent/#api-configuration-flags (default: "http://localhost:3031")',
                    'type' => 'text',
                ],
                [
                    'title' => 'Sensu Namespace',
                    'name' => 'sensu-namespace',
                    'descr' => 'The Sensu namespace that hosts exist in (default: "default")',
                    'type' => 'text',
                ],
                [
                    'title' => 'Check Prefix',
                    'name' => 'sensu-prefix',
                    'descr' => 'An optional string to prefix the checks with',
                    'type' => 'text',
                ],
                [
                    'title' => 'Source Key',
                    'name' => 'sensu-source-key',
                    'descr' => 'Should events be attributed to entities by hostname, sysName or shortname (default: hostname)',
                    'type' => 'select',
                    'options' => [
                        'hostname' => 'hostname',
                        'sysName' => 'sysName',
                        'shortname' => 'shortname',
                    ],
                    'default' => 'hostname',
                ],
            ],
            'validation' => [
                'sensu-url' => 'url',
                'sensu-source-key' => 'required|in:hostname,sysName,shortname',
            ],
        ];
    }
}
