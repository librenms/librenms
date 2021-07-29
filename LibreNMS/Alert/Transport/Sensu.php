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
 * @author Adam Bishop <adam@omega.org.uk>
 * @copyright 2020 Adam Bishop, LibreNMS
 * @license GPL
 */

namespace LibreNMS\Alert\Transport;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use LibreNMS\Alert\Transport;
use LibreNMS\Config;
use LibreNMS\Enum\AlertState;

class Sensu extends Transport
{
    // Sensu alert coding
    const OK = 0;
    const WARNING = 1;
    const CRITICAL = 2;
    const UNKNOWN = 3;

    private static $status = [
        'ok' => Sensu::OK,
        'warning' => Sensu::WARNING,
        'critical' => Sensu::CRITICAL,
    ];

    private static $severity = [
        'recovered' => AlertState::RECOVERED,
        'alert' => AlertState::ACTIVE,
        'acknowledged' => AlertState::ACKNOWLEDGED,
        'worse' => AlertState::WORSE,
        'better' => AlertState::BETTER,
    ];

    private static $client = null;

    public function deliverAlert($obj, $opts)
    {
        $sensu_opts = [];
        $sensu_opts['url'] = $this->config['sensu-url'] ? $this->config['sensu-url'] : 'http://127.0.0.1:3031';
        $sensu_opts['namespace'] = $this->config['sensu-namespace'] ? $this->config['sensu-namespace'] : 'default';
        $sensu_opts['prefix'] = $this->config['sensu-prefix'];
        $sensu_opts['source-key'] = $this->config['sensu-source-key'];

        Sensu::$client = new Client();

        try {
            return $this->contactSensu($obj, $sensu_opts);
        } catch (GuzzleException $e) {
            return 'Sending event to Sensu failed: ' . $e->getMessage();
        }
    }

    public static function contactSensu($obj, $opts)
    {
        // The Sensu agent should be running on the poller - events can be sent directly to the backend but this has not been tested, and likely needs mTLS.
        // The agent API is documented at https://docs.sensu.io/sensu-go/latest/reference/agent/#create-monitoring-events-using-the-agent-api
        if (Sensu::$client->request('GET', $opts['url'] . '/healthz')->getStatusCode() !== 200) {
            return 'Sensu API is not responding';
        }

        if ($obj['state'] !== AlertState::RECOVERED && $obj['state'] !== AlertState::ACKNOWLEDGED && $obj['alerted'] === 0) {
            // If this is the first event, send a forced "ok" dated (rrd.step / 2) seconds ago to tell Sensu the last time the check was healthy
            $data = Sensu::generateData($obj, $opts, Sensu::OK, round(Config::get('rrd.step', 300) / 2));
            Log::debug('Sensu transport sent last good event to socket: ', $data);

            $result = Sensu::$client->request('POST', $opts['url'] . '/events', ['json' => $data]);
            if ($result->getStatusCode() !== 202) {
                return $result->getReasonPhrase();
            }

            sleep(5);
        }

        $data = Sensu::generateData($obj, $opts, Sensu::calculateStatus($obj['state'], $obj['severity']));
        Log::debug('Sensu transport sent event to socket: ', $data);

        $result = Sensu::$client->request('POST', $opts['url'] . '/events', ['json' => $data]);
        if ($result->getStatusCode() === 202) {
            return true;
        }

        return $result->getReasonPhrase();
    }

    public static function generateData($obj, $opts, $status, $offset = 0)
    {
        return [
            'check' => [
                'metadata' => [
                    'name' => Sensu::checkName($opts['prefix'], $obj['name']),
                    'namespace' => $opts['namespace'],
                    'annotations' => Sensu::generateAnnotations($obj),
                ],
                'command' => sprintf('LibreNMS: %s', $obj['builder']),
                'executed' => time() - $offset,
                'interval' => Config::get('rrd.step', 300),
                'issued' => time() - $offset,
                'output' => $obj['msg'],
                'status' => $status,
            ],
            'entity' => [
                'metadata' => [
                    'name' => Sensu::getEntityName($obj, $opts['source-key']),
                    'namespace' => $opts['namespace'],
                ],
                'system' => [
                    'hostname' => $obj['hostname'],
                    'os' => $obj['os'],
                ],
            ],
        ];
    }

    public static function generateAnnotations($obj)
    {
        return array_filter([
            'generated-by' => 'LibreNMS',
            'acknowledged' => $obj['state'] === AlertState::ACKNOWLEDGED ? 'true' : 'false',
            'contact' => $obj['sysContact'],
            'description' => $obj['sysDescr'],
            'location' => $obj['location'],
            'documentation' => $obj['proc'],
            'librenms-notes' => $obj['notes'],
            'librenms-device-id' => strval($obj['device_id']),
            'librenms-rule-id' => strval($obj['rule_id']),
            'librenms-status-reason' => $obj['status_reason'],
        ], function (string $s): bool {
            return (bool) strlen($s); // strlen returns 0 for null, false or '', but 1 for integer 0 - unlike empty()
        });
    }

    public static function calculateStatus($state, $severity)
    {
        // Sensu only has a single short (status) to indicate both severity and status, so we need to map LibreNMS' state and severity onto it

        if ($state === AlertState::RECOVERED) {
            // LibreNMS alert is resolved, send ok
            return Sensu::OK;
        }

        if (array_key_exists($severity, Sensu::$status)) {
            // Severity is known, map the LibreNMS severity to the Sensu status
            return Sensu::$status[$severity];
        }

        // LibreNMS severity does not map to Sensu, send unknown
        return Sensu::UNKNOWN;
    }

    public static function getEntityName($obj, $key)
    {
        if ($key === 'shortname') {
            return Sensu::shortenName($obj['hostname']);
        }

        return $obj[$key];
    }

    public static function shortenName($name)
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

    public static function checkName($prefix, $name)
    {
        $check = strtolower(str_replace(' ', '-', $name));

        if ($prefix) {
            return sprintf('%s-%s', $prefix, $check);
        }

        return $check;
    }

    public static function configTemplate()
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
